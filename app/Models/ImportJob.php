<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ImportJob extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'status',
        'filename',
        'total_rows',
        'processed_rows',
        'skipped_rows',
        'error_message',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'status' => ImportStatus::class,
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'skipped_rows' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
