<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => RequestStatus::class
    ];

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class);
    }
}
