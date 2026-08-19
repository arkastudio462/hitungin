<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ReceiptScanService
{
    protected string $apiKey;

    protected string $model = 'qwen/qwen3.6-27b';

    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', '');
    }

    public function scan(string $imagePath): array
    {
        $base64 = $this->getBase64Image($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->baseUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $this->getPrompt(),
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64}",
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
            'max_tokens' => 2048,
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Gagal memproses struk.');
            throw new \RuntimeException($error);
        }

        $content = $response->json('choices.0.message.content', '{}');

        $json = $this->extractJson($content);

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Gagal parse response AI.');
        }

        return $this->normalize($decoded);
    }

    protected function getPrompt(): string
    {
        return <<<'PROMPT'
Kamu adalah AI yang menganalisa struk belanja/bon. Ekstrak informasi dari gambar struk ini.

PENTING: Return HANYA JSON murni tanpa markdown, tanpa penjelasan, tanpa backtick.

Contoh format output:
{"store":"Indomaret","date":"2026-08-19","type":"expense","items":[{"name":"Indomie Goreng","qty":2,"price":3500},{"name":"Aqua 600ml","qty":1,"price":4000}],"total":11000,"description":"Belanja di Indomaret - 3 item"}

Aturan:
- "type" selalu "expense" untuk struk belanja, "income" jika ada bukti pemasukan
- "date" format YYYY-MM-DD. Jika tanggal tidak terlihat, gunakan hari ini
- "price" harga satuan dalam Rupiah (angka saja, tanpa koma/titik)
- "total" total yang dibayarkan
- "description" ringkasan singkat
PROMPT;
    }

    protected function extractJson(string $content): string
    {
        $content = trim($content);

        if (str_starts_with($content, '{')) {
            return $content;
        }

        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?\s*```/s', $content, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            return $matches[0];
        }

        return $content;
    }

    protected function getBase64Image(string $path): string
    {
        $fullPath = Storage::disk('local')->path($path);

        if (! file_exists($fullPath)) {
            throw new \RuntimeException('File gambar tidak ditemukan.');
        }

        return base64_encode(file_get_contents($fullPath));
    }

    protected function getMimeType(string $path): string
    {
        $fullPath = Storage::disk('local')->path($path);
        $mime = mime_content_type($fullPath);

        return $mime ?: 'image/jpeg';
    }

    protected function normalize(array $data): array
    {
        return [
            'store' => $data['store'] ?? null,
            'date' => $this->validateDate($data['date'] ?? null),
            'type' => in_array($data['type'] ?? '', ['income', 'expense']) ? $data['type'] : 'expense',
            'items' => array_map(fn ($item) => [
                'name' => $item['name'] ?? 'Item',
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'price' => max(0, (float) ($item['price'] ?? 0)),
            ], $data['items'] ?? []),
            'total' => max(0, (float) ($data['total'] ?? 0)),
            'description' => $data['description'] ?? null,
        ];
    }

    protected function validateDate(?string $date): string
    {
        if ($date && strtotime($date) !== false) {
            return date('Y-m-d', strtotime($date));
        }

        return date('Y-m-d');
    }
}
