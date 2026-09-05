<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'cat_id', 'name'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }
}
