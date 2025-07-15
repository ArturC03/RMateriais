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

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function requestItems(): HasMany {
        return $this->hasMany(RequestItem::class);
    }

    /*
     * Returns the material that is available (not requested)
     */
    public function availableQuantity(): int {
        return $this->quantity - $this->currentlyBorrowedQuantity();
    }

    /*
     * If the item can be requested it returns true
     */
    public function isAvailable(): bool {
        return $this->availableQuantity() > 0;
    }

    /*
     * Returns the quantity of the material that is borrowed
     */
    public function currentlyBorrowedQuantity(): int {
        return $this->requestItems()
            ->where('returned', false)
            ->sum('quantity');
    }

}
