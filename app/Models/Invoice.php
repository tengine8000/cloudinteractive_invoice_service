<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'tax_rate',
        'total',
        'notes',
    ];

    /**
     * Get the User that owns the line item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lineItem for this invoice.
     */
    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
