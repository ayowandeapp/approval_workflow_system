<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['status_label'];

    protected $casts = [
        'status' => RequestStatus::class,
    ];
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
