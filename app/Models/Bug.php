<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bug extends Model
{
    protected $fillable = [
        'title',
        'project_id',
        'project',
        'priority',
        'status',
        'developer',
        'description',
        'reporter_id',
        'attachment',
        'attachment_name',
        'attachment_size',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function projectFolder()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
