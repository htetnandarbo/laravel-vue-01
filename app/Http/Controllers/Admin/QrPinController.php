<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateQrPinsRequest;
use App\Models\Qr;
use App\Services\QrPinService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrPinController extends Controller
{
    public function __construct(private readonly QrPinService $qrPinService)
    {
    }

    public function store(GenerateQrPinsRequest $request, Qr $qr)
    {
        $this->qrPinService->generate($qr, (int) $request->validated('count'));

        return back();
    }

    public function export(Qr $qr): StreamedResponse
    {
        $filename = sprintf('qr-%d-pins-%s.csv', $qr->id, now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($qr) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['qr_name', 'pin_number']);

            $qr->pins()
                ->select(['qr_id', 'pin_number'])
                ->orderBy('id')
                ->chunk(1000, function ($pins) use ($handle) {
                    foreach ($pins as $pin) {
                        fputcsv($handle, [
                            $pin->qr->name,
                            $pin->pin_number,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function checkPin(string $qr, string $pin)
    {
        $qr = Qr::where('token', $qr)->first();
        $pin = $qr->pins()->where('is_used', false)->where('pin_number', $pin)->first();

        if ($pin) {
            $pin->update(['is_used' => true]);
        }
        
        return response()->json([
            'exists' => $pin !== null,
        ]);
    }
}
