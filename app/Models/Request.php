<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Request extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',        // e.g., 'pendente', 'aprovado', 'rejeitado', 'devolvido'
        'requested_at',  // when the request was made 'approved_at',   // when the professor approved it
        'returned_at',   // when the student returned the materials
    ];

    protected $dates = [
        'requested_at',
        'approved_at',
        'returned_at'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function requestItems(): HasMany {
        return $this->hasMany(RequestItem::class);
    }

    public function isPending(): bool {
        return $this->status === 'pendente';
    }

    public function isApproved(): bool {
        return $this->status === 'aprovado';
    }

    public function isReturned(): bool {
        return $this->status === 'devolvido';
    }

    public function isRejected(): bool {
        return $this->status === 'recusado';
    }
}
