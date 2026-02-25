<?php

use App\Models\Item;
use App\Models\Qr;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeQr(array $attributes = []): Qr
{
    return Qr::query()->create(array_merge([
        'token' => 'TOKEN'.fake()->unique()->numerify('####'),
        'name' => 'Test QR',
        'status' => 'active',
    ], $attributes));
}

function makeQuestion(Qr $qr, array $attributes = []): Question
{
    return Question::query()->create(array_merge([
        'qr_id' => $qr->id,
        'label' => 'Question',
        'question_text' => 'Question',
        'type' => 'text',
        'is_required' => false,
        'options' => null,
        'sort_order' => 0,
        'order' => 0,
    ], $attributes));
}

it('validates required questions on public submit', function () {
    $qr = makeQr();
    $question = makeQuestion($qr, [
        'label' => 'Name',
        'question_text' => 'Name',
        'is_required' => true,
    ]);

    $response = $this->from("/qr/{$qr->token}")
        ->post("/qr/{$qr->token}/submit", [
            'answers' => [
                (string) $question->id => '',
            ],
        ]);

    $response->assertRedirect("/qr/{$qr->token}");
    $response->assertSessionHasErrors(["answers.{$question->id}"]);
});

it('validates select and checkbox options on public submit', function () {
    $qr = makeQr();
    $select = makeQuestion($qr, [
        'label' => 'Color',
        'question_text' => 'Color',
        'type' => 'select',
        'options' => ['red', 'blue'],
    ]);
    $checkbox = makeQuestion($qr, [
        'label' => 'Fruits',
        'question_text' => 'Fruits',
        'type' => 'checkbox',
        'options' => ['apple', 'orange'],
        'sort_order' => 1,
        'order' => 1,
    ]);

    $response = $this->from("/qr/{$qr->token}")
        ->post("/qr/{$qr->token}/submit", [
            'answers' => [
                (string) $select->id => 'green',
                (string) $checkbox->id => ['apple', 'banana'],
            ],
        ]);

    $response->assertRedirect("/qr/{$qr->token}");
    $response->assertSessionHasErrors(["answers.{$select->id}", "answers.{$checkbox->id}"]);
});

it('rejects stock transaction when item does not belong to qr', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $qrA = makeQr(['token' => 'QRA123456789']);
    $qrB = makeQr(['token' => 'QRB123456789']);
    $foreignItem = Item::query()->create([
        'qr_id' => $qrB->id,
        'name' => 'Item B',
        'sku' => 'B1',
    ]);

    $response = $this->actingAs($admin)
        ->from("/admin/qrs/{$qrA->id}")
        ->post("/admin/qrs/{$qrA->id}/stock-transactions", [
            'item_id' => $foreignItem->id,
            'type' => 'in',
            'qty' => 1,
        ]);

    $response->assertRedirect("/admin/qrs/{$qrA->id}");
    $response->assertSessionHasErrors(['item_id']);
});

it('rejects stock out when stock is insufficient', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $qr = makeQr();
    $item = Item::query()->create([
        'qr_id' => $qr->id,
        'name' => 'Prize',
        'sku' => 'P1',
    ]);

    $this->actingAs($admin)->post("/admin/qrs/{$qr->id}/stock-transactions", [
        'item_id' => $item->id,
        'type' => 'in',
        'qty' => 2,
    ]);

    $response = $this->actingAs($admin)
        ->from("/admin/qrs/{$qr->id}")
        ->post("/admin/qrs/{$qr->id}/stock-transactions", [
            'item_id' => $item->id,
            'type' => 'out',
            'qty' => 3,
        ]);

    $response->assertRedirect("/admin/qrs/{$qr->id}");
    $response->assertSessionHasErrors(['qty']);
});
