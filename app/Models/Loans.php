<?php

namespace App\Models;

use Carbon\Carbon;
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
        'loan_status',
    ];

    protected $table = 'loans';

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function books(): BelongsTo
    {
        return $this->belongsTo(Books::class);
    }

    public function monetaries(): BelongsTo
    {
        return $this->belongsTo(Monetary::class, 'id', 'loans_id');
    }
}
