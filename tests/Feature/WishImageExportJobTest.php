<?php

use App\Jobs\GenerateWishImagesZip;
use App\Models\Qr;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishImageExport;
use App\Notifications\WishImageExportCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('exports only accepted and not downloaded wish images for the selected qr', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $qr = Qr::query()->create([
        'token' => 'QR'.Str::upper(Str::random(10)),
        'name' => 'Event QR',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    $otherQr = Qr::query()->create([
        'token' => 'QR'.Str::upper(Str::random(10)),
        'name' => 'Other QR',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    File::ensureDirectoryExists(public_path('wish-cards'));

    $eligibleImage = 'wish-cards/test-eligible-'.Str::lower(Str::random(8)).'.png';
    $alreadyDownloadedImage = 'wish-cards/test-downloaded-'.Str::lower(Str::random(8)).'.png';
    $rejectedImage = 'wish-cards/test-rejected-'.Str::lower(Str::random(8)).'.png';
    $otherQrImage = 'wish-cards/test-other-'.Str::lower(Str::random(8)).'.png';

    File::put(public_path($eligibleImage), 'eligible');
    File::put(public_path($alreadyDownloadedImage), 'downloaded');
    File::put(public_path($rejectedImage), 'rejected');
    File::put(public_path($otherQrImage), 'other');

    $eligibleWish = Wish::query()->create([
        'qr_id' => $qr->id,
        'message' => 'Eligible wish',
        'image' => $eligibleImage,
        'status' => 'accepted',
        'is_downloaded' => false,
    ]);

    $alreadyDownloadedWish = Wish::query()->create([
        'qr_id' => $qr->id,
        'message' => 'Already downloaded',
        'image' => $alreadyDownloadedImage,
        'status' => 'accepted',
        'is_downloaded' => true,
    ]);

    $rejectedWish = Wish::query()->create([
        'qr_id' => $qr->id,
        'message' => 'Rejected wish',
        'image' => $rejectedImage,
        'status' => 'rejected',
        'is_downloaded' => false,
    ]);

    $otherQrWish = Wish::query()->create([
        'qr_id' => $otherQr->id,
        'message' => 'Other qr wish',
        'image' => $otherQrImage,
        'status' => 'accepted',
        'is_downloaded' => false,
    ]);

    $export = WishImageExport::query()->create([
        'qr_id' => $qr->id,
        'user_id' => $user->id,
        'status' => 'queued',
    ]);

    (new GenerateWishImagesZip($export->id))->handle();

    $export->refresh();
    expect($export->status)->toBe('completed');
    expect($export->total_images)->toBe(1);
    expect($export->file_path)->not->toBeNull();
    expect(Storage::disk('local')->exists($export->file_path))->toBeTrue();

    expect($eligibleWish->fresh()->is_downloaded)->toBeTrue();
    expect($alreadyDownloadedWish->fresh()->is_downloaded)->toBeTrue();
    expect($rejectedWish->fresh()->is_downloaded)->toBeFalse();
    expect($otherQrWish->fresh()->is_downloaded)->toBeFalse();

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
        'notifiable_type' => User::class,
        'type' => WishImageExportCompletedNotification::class,
    ]);

    Storage::disk('local')->deleteDirectory('private/wish-exports/qr-'.$qr->id);
    File::delete(public_path($eligibleImage));
    File::delete(public_path($alreadyDownloadedImage));
    File::delete(public_path($rejectedImage));
    File::delete(public_path($otherQrImage));
});
