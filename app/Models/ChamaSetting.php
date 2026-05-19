<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamaSetting extends Model
{
    protected $table = 'chama_settings';

    protected $fillable = [
        'chama_name',
        'registration_number',
        'email',
        'phone',
        'physical_address',
        'default_monthly_contribution',
        'default_loan_interest_rate',
        'currency',
        'meeting_default_location',
        'notifications_email_enabled',
        'notifications_sms_enabled',
        'loan_reminder_enabled',
        'payment_confirmation_enabled',
        'mpesa_paybill',
        'mpesa_account_name',
    ];

    protected $casts = [
        'notifications_email_enabled' => 'boolean',
        'notifications_sms_enabled' => 'boolean',
        'loan_reminder_enabled' => 'boolean',
        'payment_confirmation_enabled' => 'boolean',
        'default_monthly_contribution' => 'decimal:2',
        'default_loan_interest_rate' => 'decimal:2',
    ];

    public static function getInstance()
    {
        return self::first() ?? self::create([
            'chama_name' => config('app.name', 'Chama'),
            'registration_number' => 'N/A',
            'email' => config('app.url'),
            'phone' => 'N/A',
            'physical_address' => 'N/A',
        ]);
    }
}
