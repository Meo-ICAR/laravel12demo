<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Call extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_chiamato',
        'data_inizio',
        'durata',
        'stato_chiamata',
        'esito',
        'utente',
        'company_id',
    ];

    protected $casts = [
        'data_inizio' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Helper method to get duration in seconds
    public function getDurationInSeconds()
    {
        if (!$this->durata) {
            return 0;
        }

        $parts = explode(':', $this->durata);
        if (count($parts) === 2) {
            // Format: MM:SS
            return (int)$parts[0] * 60 + (int)$parts[1];
        } elseif (count($parts) === 3) {
            // Format: HH:MM:SS
            return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
        }

        return 0;
    }

    // Helper method to format duration for display
    public function getFormattedDuration()
    {
        $seconds = $this->getDurationInSeconds();

        if ($seconds === 0) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            return sprintf('%02d:%02d', $minutes, $secs);
        }
    }
}
