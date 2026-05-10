<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPosArticle extends Model
{
    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function article()
    {
        return $this->belongsTo(PosArticle::class, 'pos_article_id');
    }
}
