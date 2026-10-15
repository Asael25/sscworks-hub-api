<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $table = 'document_versions';

    protected $fillable = [
        'document_id',
        'version',
        'file_name',
        'file_url',
        'size',
        'uploaded_by',
        'uploaded_date',
        'note',
    ];

    protected $casts = [
        'uploaded_date' => 'date:Y-m-d',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
}
