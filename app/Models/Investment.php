<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'amount_invested',
        'expected_return',
        'profit',
        'investment_type',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'amount_invested' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'profit' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes()
    {
        return $this->hasMany(InvestmentVote::class);
    }

    public function userVote()
    {
        return $this->hasOne(InvestmentVote::class)->where('user_id', auth()->id());
    }
}
