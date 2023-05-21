<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorHeartbeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'last_seen'
    ];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
