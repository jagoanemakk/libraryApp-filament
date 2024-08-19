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

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($loans) {
    //         $today = Carbon::today();
    //         $dueDate = $this->due_date;

    //         if ($dueDate > $today) {
    //             $loans->loan_status = 'Expired';
    //         } else if ($dueDate == $today) {
    //             $loans->loan_status = 'Today';
    //         } else {
    //             $daysLeft = $dueDate->diffInDays($today);
    //             $loans->loan_status = "{$daysLeft} Hari";
    //         }
    //     });
    // }

    protected $fillable =
    [
        'user_id',
        'books_id',
        'due_date',
        'loan_status'
    ];

    protected $casts = [
        'due_date' => 'date:YYYY-MM-DD',
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
