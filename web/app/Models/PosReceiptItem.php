<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosReceiptItem extends Model
{
    protected $guarded = [];

    public function receipt()
    {
        return $this->belongsTo(PosReceipt::class, 'pos_receipt_id');
    }

    public function article()
    {
        return $this->belongsTo(PosArticle::class, 'pos_article_id');
    }
}
