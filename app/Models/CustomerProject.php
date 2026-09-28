<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProject extends Model
{
    use HasFactory;

    protected $table = 'customer_projects';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'customer', 'name', 'contract_number', 'po_number', 'unit',
        'location', 'contract_value', 'target', 'actual', 'deadline',
        'start_date', 'period', 'status', 'pic', 'payment_term', 'retention'
    ];

    public function scopes()
    {
        return $this->hasMany(CustomerProjectScope::class, 'project_id', 'id');
    }

    public function weeklyProgresses()
    {
        return $this->hasMany(CustomerProjectWeeklyProgress::class, 'project_id', 'id');
    }

    public function issues()
    {
        return $this->hasMany(CustomerProjectIssue::class, 'project_id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(CustomerProjectPhoto::class, 'project_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(CustomerProjectApproval::class, 'project_id', 'id');
    }
}
