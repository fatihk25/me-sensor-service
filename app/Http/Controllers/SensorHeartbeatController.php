<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorHeartbeat;
use Exception;
use Illuminate\Http\Request;

class SensorHeartbeatController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sensors-api', ['only' => ['heartbeat']]);
    }
    
    public function heartbeat(Request $request) 
    {
        try {
            $request->validate([
                'uuid' => 'required'
            ]);

            $credential = $request->input('uuid');
            $sensor = Sensor::where('uuid', $credential)->first();
            $data = SensorHeartbeat::where('sensor_id', $sensor->id)->first();
            if(!$data) {
                $data = new SensorHeartbeat();
                $data->sensor_id = $sensor->id;
                $data->last_seen = now();
                $data->save();
                return response()->json([
                    'code' => 200,
                    'message' => 'OK',
                    'data' => $data,
                ],200);
            } else {
                $data->last_seen = now();
                $data->save();
                return response()->json([
                    'code' => 200,
                    'message' => 'OK',
                    'data' => $data,
                ],200);
            }
        } catch(Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
