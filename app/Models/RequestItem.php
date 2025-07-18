<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestItem extends Model
{
    use HasFactory;
    protected $fillable = [
        "request_id",
        "material_id",
        "quantity",
        "requested_days",
        "due_date",
        "returned"
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'returned' => 'boolean',
    ];

    protected $appends = ['is_borrowed'];

    public function material(): BelongsTo {
        return $this->belongsTo(Material::class);
    }

    public function request(): BelongsTo{
        return $this->belongsTo(Request::class);
    }

    public function isOverdue(): bool {
        return !$this->returned && now()->gt($this->due_date);
    }

    public function isReturned(): bool {
        return $this->returned;
    }

    public function putInCartOf(User $user) {
        $cart = $user->cart();

        return $this->update([
            'request_id' => $cart->id
        ]);
    }

    public function GetIsBorrowedAttribute(): bool {
        return !$this->isReturned() && optional($this->request)->status !== 'rascunho';
    }

}
