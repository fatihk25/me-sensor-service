<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_uuid',
        'file'
    ];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
