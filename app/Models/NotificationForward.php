<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationForward extends Model
{
    protected $fillable = [
        'user_id',
        'package_name',
        'title',
        'message',
        'parsed_data',
        'transaction_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'parsed_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function markParsed(array $data): void
    {
        $this->update([
            'parsed_data' => $data,
            'status' => 'parsed',
        ]);
    }

    public function markConfirmed(Transaction $transaction): void
    {
        $this->update([
            'transaction_id' => $transaction->id,
            'status' => 'confirmed',
        ]);
    }

    public function markIgnored(): void
    {
        $this->update(['status' => 'ignored']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
