<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemEvent extends Model
{
    protected $fillable = [
        'type',
        'message',
        'context',
        'severity',
        'is_error_log',
        'resolved',
        'admin_notes',
        'acknowledged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved' => 'boolean',
        'is_error_log' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Scope for filtering error logs only.
     */
    public function scopeErrors($query)
    {
        return $query->where('is_error_log', true);
    }

    /**
     * Scope for filtering by severity.
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for unresolved events.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope for filtering by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
