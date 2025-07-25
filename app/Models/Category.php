<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ["name"];

    public function materials(): HasMany {
        return $this->hasMany(Material::class);
    }
}
