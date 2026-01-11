<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    

    protected $fillable = [
        'type',
        'amount',
        'description',
        'date', // Ensure this is included
        'is_recurring',
        'recurring_interval',
        'category_id',
        'account_id',
        'user_id',
    ];

    // Cast the 'date' attribute to a Carbon instance
    protected $casts = [
        'date' => 'date', // This will cast the 'date' attribute to a Carbon instance
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
