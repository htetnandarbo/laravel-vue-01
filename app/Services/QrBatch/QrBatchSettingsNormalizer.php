<?php

namespace App\Services\QrBatch;

use Illuminate\Validation\ValidationException;

class QrBatchSettingsNormalizer
{
    public const DEFAULT_PAGE_FORMAT = 'A4';
    public const DEFAULT_MARGIN_MM = 8.0;
    public const DEFAULT_GAP_MM = 4.0;
    public const DEFAULT_COLS = 4;
    public const DEFAULT_ROWS = 6;
    public const DEFAULT_SIZE_MODE = 'preset';
    public const DEFAULT_SIZE_PRESET = 'M';

    public const SIZE_PRESETS = [
        'S' => 25.0,
        'M' => 30.0,
        'L' => 40.0,
    ];

    private const PAGE_DIMENSIONS_MM = [
        'A4' => ['width' => 210.0, 'height' => 297.0],
        'LETTER' => ['width' => 215.9, 'height' => 279.4],
    ];

    public function normalize(array $validated): array
    {
        $pageFormat = self::DEFAULT_PAGE_FORMAT;
        $marginMm = self::DEFAULT_MARGIN_MM;
        $gapMm = self::DEFAULT_GAP_MM;
        $cols = self::DEFAULT_COLS;
        $rows = self::DEFAULT_ROWS;
        $sizeMode = strtolower((string) ($validated['size_mode'] ?? self::DEFAULT_SIZE_MODE));
        $baseUrl = trim((string) $validated['base_url']);

        $sizeMm = $this->resolveSizeMm($sizeMode, $validated);

        $this->validateFitsPage(
            pageFormat: $pageFormat,
            marginMm: $marginMm,
            gapMm: $gapMm,
            cols: $cols,
            rows: $rows,
            sizeMm: $sizeMm,
        );

        return [
            'quantity' => 1,
            'status' => 'pending',
            'base_url' => $baseUrl,
            'page_format' => $pageFormat,
            'margin_mm' => $marginMm,
            'gap_mm' => $gapMm,
            'cols' => $cols,
            'rows' => $rows,
            'size_mode' => $sizeMode,
            'size_mm' => $sizeMm,
        ];
    }

    public static function defaults(): array
    {
        return [
            'page_format' => self::DEFAULT_PAGE_FORMAT,
            'margin_mm' => self::DEFAULT_MARGIN_MM,
            'gap_mm' => self::DEFAULT_GAP_MM,
            'cols' => self::DEFAULT_COLS,
            'rows' => self::DEFAULT_ROWS,
            'size_mode' => self::DEFAULT_SIZE_MODE,
            'size_preset' => self::DEFAULT_SIZE_PRESET,
            'size_mm' => self::SIZE_PRESETS[self::DEFAULT_SIZE_PRESET],
        ];
    }

    private function resolveSizeMm(string $sizeMode, array $validated): float
    {
        if ($sizeMode === 'custom') {
            if (! array_key_exists('size_mm', $validated) || $validated['size_mm'] === null || $validated['size_mm'] === '') {
                throw ValidationException::withMessages([
                    'size_mm' => 'The size_mm field is required when size_mode is custom.',
                ]);
            }

            return (float) $validated['size_mm'];
        }

        $preset = strtoupper((string) ($validated['size_preset'] ?? self::DEFAULT_SIZE_PRESET));
        $sizeMm = self::SIZE_PRESETS[$preset] ?? null;

        if ($sizeMm === null) {
            throw ValidationException::withMessages([
                'size_preset' => 'Invalid size preset.',
            ]);
        }

        return $sizeMm;
    }

    private function validateFitsPage(
        string $pageFormat,
        float $marginMm,
        float $gapMm,
        int $cols,
        int $rows,
        float $sizeMm,
    ): void {
        $page = self::PAGE_DIMENSIONS_MM[$pageFormat] ?? null;

        if (! $page) {
            throw ValidationException::withMessages([
                'page_format' => 'Unsupported page format.',
            ]);
        }

        $requiredWidth = ($marginMm * 2) + ($cols * $sizeMm) + (max(0, $cols - 1) * $gapMm);
        $requiredHeight = ($marginMm * 2) + ($rows * $sizeMm) + (max(0, $rows - 1) * $gapMm);

        if ($requiredWidth > $page['width']) {
            throw ValidationException::withMessages([
                'cols' => sprintf(
                    'Layout width %.2fmm exceeds %s width %.2fmm. Reduce cols/size/gap/margin.',
                    $requiredWidth,
                    $pageFormat,
                    $page['width']
                ),
            ]);
        }

        if ($requiredHeight > $page['height']) {
            throw ValidationException::withMessages([
                'rows' => sprintf(
                    'Layout height %.2fmm exceeds %s height %.2fmm. Reduce rows/size/gap/margin.',
                    $requiredHeight,
                    $pageFormat,
                    $page['height']
                ),
            ]);
        }
    }
}
