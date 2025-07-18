<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "quantity",
        "max_days_per_request"
    ];

    protected $appends = [
        'available_quantity',
        'is_available',
        'currently_borrowed_quantity'
    ];

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function requestItems(): HasMany {
        return $this->hasMany(RequestItem::class);
    }

    /*
     * Returns the material that is available (not requested)
     */
    public function getAvailableQuantityAttribute(): int {
        return $this->quantity - $this->currently_borrowed_quantity;
    }

    /*
     * If the item can be requested it returns true
     */
    public function getIsAvailableAttribute(): bool {
        return $this->available_quantity > 0;
    }

    /*
     * Returns the quantity of the material that is borrowed
     */
    public function getCurrentlyBorrowedQuantityAttribute(): int {
        return $this->requestItems->where('is_borrowed', true)->sum('quantity');
    }
}
