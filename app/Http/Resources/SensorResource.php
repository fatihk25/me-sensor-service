<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SensorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'organization_id' => $this->organization_id,
            'protected_subnet' => $this->protected_subnet,
            'external_subnet' => $this->external_subnet,
            'mqtt_topic' => $this->mqtt_topic,
            'mqtt_ip' => $this->mqtt_ip,
            'mqtt_port' => $this->mqtt_port,
            'network_interface' => $this->network_interface,
            'uuid' => $this->uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
