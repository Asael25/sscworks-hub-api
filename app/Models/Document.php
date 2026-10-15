<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'file_name',
        'file_url',
        'module',
        'category',
        'related_request',
        'related_title',
        'unit',
        'file_type',
        'size',
        'uploaded_by',
        'uploaded_date',
        'period',
        'version',
        'status',
        'expires_at',
        'description',
        'can_upload_version',
        'can_delete',
        'locked',
        'metadata',
    ];

    protected $casts = [
        'can_upload_version' => 'boolean',
        'can_delete' => 'boolean',
        'locked' => 'boolean',
        'metadata' => 'array',
        'uploaded_date' => 'date:Y-m-d',
        'expires_at' => 'date:Y-m-d',
    ];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id', 'id')->orderBy('id', 'desc');
    }
}
