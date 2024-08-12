<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loans extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'user_id',
        'books_id',
        'due_date',
        'loan_status'
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function books(): BelongsTo
    {
        return $this->belongsTo(Books::class);
    }
}
