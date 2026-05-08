<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'wallet_config' => 'array',
        'prices_include_tax' => 'boolean',
        'tax_exempt' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Calculate tax amount from a gross amount (Brutto → MwSt.)
     */
    public function calculateTaxFromGross(float $gross): float
    {
        if ($this->tax_exempt) return 0.0;
        $rate = $this->tax_rate / 100;
        return round($gross - ($gross / (1 + $rate)), 2);
    }

    /**
     * Calculate tax amount from a net amount (Netto → MwSt.)
     */
    public function calculateTaxFromNet(float $net): float
    {
        if ($this->tax_exempt) return 0.0;
        return round($net * ($this->tax_rate / 100), 2);
    }
}
