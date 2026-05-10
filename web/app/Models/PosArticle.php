<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosArticle extends Model
{
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(PosArticleCategory::class, 'category_id');
    }
}
