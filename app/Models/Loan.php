<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'guarantor_id',
        'loan_amount',
        'interest_rate',
        'total_repayable',
        'monthly_payment',
        'duration_months',
        'payments_made',
        'purpose',
        'approval_status',
        'repayment_status',
        'approved_by',
        'approval_date',
        'disbursement_date',
        'maturity_date',
        'rejection_reason',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_repayable' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'maturity_date' => 'date',
        'payments_made' => 'integer',
    ];

    public function borrower()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guarantor()
    {
        return $this->belongsTo(User::class, 'guarantor_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals()
    {
        return $this->hasMany(LoanApproval::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
