<?php

namespace App\Services;

use App\Models\NotificationForward;
use Illuminate\Support\Facades\Http;

class NotificationParseService
{
    protected string $apiKey;

    protected string $model = 'qwen/qwen3.6-27b';

    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    protected array $knownApps = [
        'com.bca' => ['name' => 'BCA Mobile', 'type' => 'bank'],
        'com.bri' => ['name' => 'BRImo', 'type' => 'bank'],
        'com.bni' => ['name' => 'BNI Mobile Banking', 'type' => 'bank'],
        'com.mandiri' => ['name' => 'Livin by Mandiri', 'type' => 'bank'],
        'com.cimb' => ['name' => 'CIMB Clicks', 'type' => 'bank'],
        'com.danamon' => ['name' => 'Danamon Online', 'type' => 'bank'],
        'com.bsi' => ['name' => 'BSI Mobile', 'type' => 'bank'],
        'com.mega' => ['name' => 'Mega Mobile', 'type' => 'bank'],
        'com.gopay.gopayapp' => ['name' => 'GoPay', 'type' => 'ewallet'],
        'com.gopajj.gopajj' => ['name' => 'OVO', 'type' => 'ewallet'],
        'com.dana' => ['name' => 'DANA', 'type' => 'ewallet'],
        'com.shopeepay' => ['name' => 'ShopeePay', 'type' => 'ewallet'],
        'com.linkaja' => ['name' => 'LinkAja', 'type' => 'ewallet'],
    ];

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', '');
    }

    public function parse(NotificationForward $forward): array
    {
        $appName = $this->knownApps[$forward->package_name]['name'] ?? $forward->package_name;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->baseUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->getPrompt($appName, $forward->title, $forward->message),
                ],
            ],
            'temperature' => 0.1,
            'max_completion_tokens' => 1024,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Gagal memproses notifikasi.');
            throw new \RuntimeException($error);
        }

        $content = $response->json('choices.0.message.content', '{}');

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Gagal parse response AI.');
        }

        return $this->normalize($decoded);
    }

    protected function getPrompt(string $appName, ?string $title, string $message): string
    {
        return <<<PROMPT
Kamu adalah asisten pencatatan keuangan. Analisa notifikasi dari aplikasi "{$appName}" berikut dan ekstrak informasi transaksi.

Judul notifikasi: {$title}
Isi notifikasi: {$message}

Return HANYA JSON tanpa penjelasan tambahan.

Format JSON:
{
  "type": "income" atau "expense",
  "amount": 50000,
  "description": "Deskripsi singkat transaksi",
  "date": "YYYY-MM-DD",
  "merchant": "Nama merchant/penerima/pengirim",
  "category_guess": "Salah satu dari: gaji, transfer, belanja, makanan, transport, tagihan, hiburan, kesehatan, pendidikan, investasi, lainnya"
}

Aturan:
- "type": "expense" jika uang keluar (pembayaran, transfer keluar, belanja), "income" jika uang masuk (transfer masuk, gaji, refund)
- "amount": jumlah uang dalam Rupiah (angka saja, tanpa koma/titik). Jika ada beberapa nominal, gunakan nominal utama
- "description": ringkasan singkat dalam Bahasa Indonesia (maks 50 karakter)
- "date": format YYYY-MM-DD. Jika tidak ada tanggal, gunakan hari ini
- "merchant": nama penerima/pengirim/merchant. Jika tidak ada, null
- "category_guess": tebakan kategori transaksi
- Jika notifikasi bukan transaksi keuangan (misal: promo, iklan, update app), return: {"type": "ignore"}
PROMPT;
    }

    protected function normalize(array $data): array
    {
        if (($data['type'] ?? '') === 'ignore') {
            return ['type' => 'ignore'];
        }

        return [
            'type' => in_array($data['type'] ?? '', ['income', 'expense']) ? $data['type'] : 'expense',
            'amount' => max(0, (float) ($data['amount'] ?? 0)),
            'description' => mb_substr($data['description'] ?? 'Transaksi dari notifikasi', 0, 255),
            'date' => $this->validateDate($data['date'] ?? null),
            'merchant' => $data['merchant'] ?? null,
            'category_guess' => $data['category_guess'] ?? 'lainnya',
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
