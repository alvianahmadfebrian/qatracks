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
        'deadline',
        'description',
        'reporter_id',
        'attachment',
        'attachment_name',
        'attachment_size',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function isOverdue(): bool
    {
        if (! $this->deadline) {
            return false;
        }

        $resolvedStatuses = ['done_by_development', 'done_by_support_qa', 'fixed', 'resolved', 'closed'];

        return ! in_array($this->status, $resolvedStatuses) && $this->deadline->isPast() && ! $this->deadline->isToday();
    }

    public function isDueToday(): bool
    {
        if (! $this->deadline) {
            return false;
        }

        $resolvedStatuses = ['done_by_development', 'done_by_support_qa', 'fixed', 'resolved', 'closed'];

        return ! in_array($this->status, $resolvedStatuses) && $this->deadline->isToday();
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function projectFolder()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
