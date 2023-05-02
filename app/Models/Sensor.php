<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Sensor extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
        'protected_subnet',
        'external_subnet',
        'mqtt_topic',
        'mqtt_ip',
        'mqtt_port',
        'network_interface',
        'uuid',
        'update_status'
    ];

    public function SensorHeartbeat()
    {
        return $this->hasMany(SensorHeartbeat::class);
    }

    public function SensorRule()
    {
        return $this->hasMany(SensorRule::class);
    }

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
