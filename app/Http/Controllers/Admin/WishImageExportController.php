<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateWishImagesZip;
use App\Models\Qr;
use App\Models\WishImageExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WishImageExportController extends Controller
{
    public function index(Request $request, Qr $qr): JsonResponse
    {
        $this->ensureAdmin($request);

        $exports = WishImageExport::query()
            ->where('qr_id', $qr->id)
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json([
            'exports' => $exports->map(fn (WishImageExport $export) => $this->toPayload($export))->values(),
        ]);
    }

    public function store(Request $request, Qr $qr): JsonResponse
    {
        $this->ensureAdmin($request);

        $existing = WishImageExport::query()
            ->where('qr_id', $qr->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['queued', 'processing'])
            ->latest('id')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'An image export is already in progress.',
                'export' => $this->toPayload($existing),
            ], 202);
        }

        $export = WishImageExport::query()->create([
            'qr_id' => $qr->id,
            'user_id' => $request->user()->id,
            'status' => 'queued',
        ]);

        GenerateWishImagesZip::dispatch($export->id);

        return response()->json([
            'message' => 'Wish image export has been queued.',
            'export' => $this->toPayload($export),
        ], 202);
    }

    public function download(Request $request, WishImageExport $wishImageExport): BinaryFileResponse
    {
        $this->ensureAdmin($request);

        abort_unless((int) $wishImageExport->user_id === (int) $request->user()->id, 403);

        if (
            $wishImageExport->status !== 'completed'
            || ! $wishImageExport->file_path
            || ! Storage::disk('local')->exists($wishImageExport->file_path)
        ) {
            throw ValidationException::withMessages([
                'download' => 'The ZIP file is not ready for download.',
            ]);
        }

        return response()->download(
            Storage::disk('local')->path($wishImageExport->file_path),
            basename($wishImageExport->file_path)
        );
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }

    private function toPayload(WishImageExport $export): array
    {
        $downloadAvailable = (bool) (
            $export->status === 'completed'
            && $export->file_path
            && Storage::disk('local')->exists($export->file_path)
        );

        return [
            'id' => $export->id,
            'status' => $export->status,
            'total_images' => (int) $export->total_images,
            'error_message' => $export->error_message,
            'download_available' => $downloadAvailable,
            'download_url' => $downloadAvailable ? route('admin.wish-image-exports.download', $export) : null,
            'created_at' => optional($export->created_at)->toISOString(),
            'updated_at' => optional($export->updated_at)->toISOString(),
            'started_at' => optional($export->started_at)->toISOString(),
            'finished_at' => optional($export->finished_at)->toISOString(),
        ];
    }
}
