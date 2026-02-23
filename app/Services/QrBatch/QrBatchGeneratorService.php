<?php

namespace App\Services\QrBatch;

use App\Models\QrBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use TCPDF;
use ZipArchive;

class QrBatchGeneratorService
{
    private const INSERT_CHUNK_SIZE = 1000;
    private const MAX_CODES_PER_PDF = 10000;
    private const PROGRESS_SCALE = 1000;
    private const TOKEN_STAGE_WEIGHT = 600;
    private const PDF_STAGE_WEIGHT = 350;
    private const ZIP_STAGE_WEIGHT = 50;

    public function generate(QrBatch $batch): QrBatch
    {
        $this->resetBatch($batch);
        $this->generateTokens($batch);
        $pdfPath = $this->generatePdfArtifacts($batch);

        $batch->update([
            'status' => 'completed',
            'pdf_path' => $pdfPath,
            'progress_current' => self::PROGRESS_SCALE,
            'progress_total' => self::PROGRESS_SCALE,
            'progress_percent' => 100,
            'status_message' => 'Completed',
            'finished_at' => now(),
        ]);

        return $batch->refresh();
    }

    private function resetBatch(QrBatch $batch): void
    {
        $batch->items()->delete();
        Storage::disk('local')->deleteDirectory($this->batchDirectory($batch));

        $batch->update([
            'status' => 'processing',
            'pdf_path' => null,
            'progress_current' => 0,
            'progress_total' => self::PROGRESS_SCALE,
            'progress_percent' => 0,
            'status_message' => 'Starting',
            'started_at' => now(),
            'finished_at' => null,
        ]);
    }

    private function generateTokens(QrBatch $batch): void
    {
        $rows = [];
        $inserted = 0;
        $this->updateProgress($batch, 0, 'Generating tokens');

        for ($sequence = 1; $sequence <= $batch->quantity; $sequence++) {
            $token = Str::lower(Str::random(128));

            $rows[] = [
                'qr_batch_id' => $batch->id,
                'sequence' => $sequence,
                'token' => $token,
                'url' => $this->buildQrUrl($batch->base_url, $token),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($rows) >= self::INSERT_CHUNK_SIZE) {
                DB::table('qr_batch_items')->insert($rows);
                $inserted += count($rows);
                $rows = [];
                $this->updateTokenProgress($batch, $inserted);
            }
        }

        if ($rows !== []) {
            DB::table('qr_batch_items')->insert($rows);
            $inserted += count($rows);
        }

        $this->updateTokenProgress($batch, $inserted);
    }

    private function generatePdfArtifacts(QrBatch $batch): string
    {
        $folder = $this->batchDirectory($batch);
        Storage::disk('local')->makeDirectory($folder);

        $partPaths = [];
        $partNumber = 1;
        $totalParts = (int) ceil($batch->quantity / self::MAX_CODES_PER_PDF);

        for ($start = 1; $start <= $batch->quantity; $start += self::MAX_CODES_PER_PDF) {
            $end = min($batch->quantity, $start + self::MAX_CODES_PER_PDF - 1);

            $items = DB::table('qr_batch_items')
                ->where('qr_batch_id', $batch->id)
                ->whereBetween('sequence', [$start, $end])
                ->orderBy('sequence')
                ->get(['sequence', 'url']);

            $this->updatePdfProgress($batch, $partNumber - 1, $totalParts);
            $partPaths[] = $this->renderPdfPart($batch, $items->all(), $partNumber);
            $this->updatePdfProgress($batch, $partNumber, $totalParts);
            $partNumber++;
        }

        $this->updateProgress(
            $batch,
            self::TOKEN_STAGE_WEIGHT + self::PDF_STAGE_WEIGHT,
            'Packaging ZIP'
        );

        return $this->zipParts($batch, $partPaths);
    }

    /**
     * @param  array<int, object>  $items
     */
    private function renderPdfPart(QrBatch $batch, array $items, int $partNumber): string
    {
        $pdf = new TCPDF('P', 'mm', $batch->page_format, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCreator(config('app.name', 'Laravel'));
        $pdf->SetAuthor(config('app.name', 'Laravel'));
        $pdf->SetTitle("QR Batch {$batch->id} Part {$partNumber}");

        $perPage = max(1, $batch->cols * $batch->rows);
        $slotIndex = 0;

        $style = [
            'border' => 0,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];

        foreach ($items as $item) {
            if ($slotIndex % $perPage === 0) {
                $pdf->AddPage();
            }

            $position = $slotIndex % $perPage;
            $colIndex = $position % $batch->cols;
            $rowIndex = intdiv($position, $batch->cols);

            $x = $batch->margin_mm + ($colIndex * ($batch->size_mm + $batch->gap_mm));
            $y = $batch->margin_mm + ($rowIndex * ($batch->size_mm + $batch->gap_mm));

            $pdf->write2DBarcode((string) $item->url, 'QRCODE,H', $x, $y, $batch->size_mm, $batch->size_mm, $style, 'N');
            $slotIndex++;
        }

        $relativePath = sprintf('%s/batch-%d-part-%03d.pdf', $this->batchDirectory($batch), $batch->id, $partNumber);
        $absolutePath = Storage::disk('local')->path($relativePath);
        $pdf->Output($absolutePath, 'F');

        return $relativePath;
    }

    /**
     * @param  array<int, string>  $partPaths
     */
    private function zipParts(QrBatch $batch, array $partPaths): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required to package multiple PDF files.');
        }

        $relativePath = sprintf('%s/batch-%d.zip', $this->batchDirectory($batch), $batch->id);
        $absolutePath = Storage::disk('local')->path($relativePath);

        $zip = new ZipArchive();
        $result = $zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new RuntimeException('Unable to create ZIP archive for QR batch.');
        }

        foreach ($partPaths as $partPath) {
            $zip->addFile(Storage::disk('local')->path($partPath), basename($partPath));
        }

        $zip->close();

        $this->updateProgress($batch, self::PROGRESS_SCALE - 1, 'Finalizing');

        return $relativePath;
    }

    private function buildQrUrl(string $baseUrl, string $token): string
    {
        if (str_contains($baseUrl, '{token}')) {
            return str_replace('{token}', $token, $baseUrl);
        }

        if (Str::endsWith($baseUrl, ['/', '='])) {
            return $baseUrl.$token;
        }

        return $baseUrl.'/'.$token;
    }

    private function batchDirectory(QrBatch $batch): string
    {
        return "qr-batches/{$batch->id}";
    }

    private function updateTokenProgress(QrBatch $batch, int $inserted): void
    {
        $ratio = $batch->quantity > 0 ? min(1, $inserted / $batch->quantity) : 1;
        $current = (int) round(self::TOKEN_STAGE_WEIGHT * $ratio);
        $this->updateProgress($batch, $current, "Generating tokens ({$inserted}/{$batch->quantity})");
    }

    private function updatePdfProgress(QrBatch $batch, int $completedParts, int $totalParts): void
    {
        $ratio = $totalParts > 0 ? min(1, $completedParts / $totalParts) : 1;
        $current = self::TOKEN_STAGE_WEIGHT + (int) round(self::PDF_STAGE_WEIGHT * $ratio);
        $this->updateProgress($batch, $current, "Rendering PDFs ({$completedParts}/{$totalParts})");
    }

    private function updateProgress(QrBatch $batch, int $current, string $message): void
    {
        $current = max(0, min(self::PROGRESS_SCALE, $current));
        $percent = (int) floor(($current / self::PROGRESS_SCALE) * 100);

        $batch->forceFill([
            'status' => 'processing',
            'progress_current' => $current,
            'progress_total' => self::PROGRESS_SCALE,
            'progress_percent' => $percent,
            'status_message' => $message,
        ])->save();
    }
}
