<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAddendum extends Model
{
    use HasFactory;

    protected $table = 'contract_addenda';

    protected $fillable = [
        'addendum_id',
        'module',
        'target_item_id',
        'addendum_number',
        'approval_id',
        'previous_amount',
        'revised_amount',
        'previous_target_date',
        'revised_target_date',
        'reason',
        'document_name',
        'document_url',
        'status',
        'submitted_by',
        'submitted_date',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'previous_amount' => 'float',
        'revised_amount' => 'float',
        'addendum_number' => 'integer',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id', 'id');
    }
}
