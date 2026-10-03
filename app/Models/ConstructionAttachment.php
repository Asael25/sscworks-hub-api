<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionAttachment extends Model
{
    use HasFactory;

    protected $table = 'construction_attachments';

    protected $fillable = [
        'work_id',
        'file_name',
        'file_type',
        'file_size_kb',
        'file_url',
        'uploaded_by',
        'uploaded_at',
    ];

    public function work()
    {
        return $this->belongsTo(ConstructionWork::class, 'work_id', 'id');
    }
}
