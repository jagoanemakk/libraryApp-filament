<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Monetary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'books_id',
        'loans_id',
        'fee',
        'status',
    ];

    protected $table = 'monetary';

    public function loans(): BelongsTo
    {
        return $this->belongsTo(Loans::class);
    }
}
