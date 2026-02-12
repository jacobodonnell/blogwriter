<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemEvent extends Model
{
    protected $casts = [
        'context' => 'array',
        'resolved' => 'boolean',
        'is_error_log' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Scope for filtering error logs only.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function errors($query)
    {
        return $query->where('is_error_log', true);
    }

    /**
     * Scope for filtering by severity.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function severity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for unresolved events.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function unresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope for filtering by event type.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function ofType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
