<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property \Illuminate\Support\Carbon $due_date
 * @property Material $material
 * @property \App\Models\Request $request
 * @property int id
 * @property bool returned
 * @property int $id
 * @property int $request_id
 * @property int $material_id
 * @property int $quantity
 * @property bool $returned
 * @property \Illuminate\Support\Carbon|null $reserved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\RequestItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereReservedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RequestItem extends Model
{
    use HasFactory;
    protected $fillable = [
        "request_id",
        "material_id",
        "quantity",
        "due_date",
        "reserved_at",
        "returned"
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reserved_at' => 'datetime',
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

    public function isReserved(): bool {
        return !is_null($this->reserved_at);
    }

    public function putInCartOf(User $user): bool
    {
        $cart = $user->cart();

        return $this->update([
            'request_id' => $cart->id
        ]);
    }

    public function GetIsBorrowedAttribute(): bool {
        return !$this->isReturned() && optional($this->request)->status !== 'rascunho';
    }

    public function refreshDueDate(): void {
        // Fixed 3-day duration as per client requirements
        $this->due_date = now()->addDays(3);
        $this->save();
    }

    public function refreshReturnDates(): void {
        // This method is kept for compatibility but now uses fixed 3-day duration
        $this->refreshDueDate();
    }
}
