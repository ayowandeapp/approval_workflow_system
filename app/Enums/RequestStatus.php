<?php


namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = '0';
    case APPROVED = '2';
    case REJECTED = '3';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
        };
    }
}