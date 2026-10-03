<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProjectAttachment extends Model
{
    use HasFactory;

    protected $table = 'customer_project_attachments';

    protected $fillable = [
        'project_id',
        'file_name',
        'file_type',
        'file_size_kb',
        'file_url',
        'uploaded_by',
        'uploaded_at',
    ];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
