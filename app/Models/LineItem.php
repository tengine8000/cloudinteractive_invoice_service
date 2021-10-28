<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class LineItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'invoice_id',
        'description',
        'rate',
        'quantity',
        'additional_info',
    ];

    /**
     * Get the invoice that owns the line item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
