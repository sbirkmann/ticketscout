<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosArticleCategory extends Model
{
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function articles()
    {
        return $this->hasMany(PosArticle::class, 'category_id');
    }
}
