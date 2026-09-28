<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProjectScope extends Model
{
    protected $table = 'customer_project_scopes';
    protected $fillable = ['project_id', 'scope', 'weight', 'target', 'actual'];

    public function project()
    {
        return $this->belongsTo(CustomerProject::class, 'project_id', 'id');
    }
}
