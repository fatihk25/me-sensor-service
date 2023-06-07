<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorHeartbeat;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'uuid' => 'required',
                'isActive' => 'boolean'
            ]);

            // dd($request->input('isActive'));

            $credential = $request->input('uuid');
            $sensor = Sensor::where('uuid', $credential)->first();
            $data = SensorHeartbeat::create([
                'sensor_id' => $sensor->id,
                'last_seen' => Carbon::now(),
                'isActive' => $request->input('isActive')
            ]);
            $data = SensorHeartbeat::where('sensor_id', $sensor->id)->get();
            return response()->json([
                'code' => 201,
                'message' => 'success',
                'data' => $data
            ],201);
        } catch(Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ],500);
        }
    }

    public function getLog($id) {
        try {
            $data = DB::table('sensors')
            ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
            ->join('sensor_heartbeats', 'sensors.id', '=', 'sensor_heartbeats.sensor_id')
            ->select('sensors.id','sensors.uuid', 'sensors.name',  'sensor_heartbeats.isActive', 'sensor_heartbeats.last_seen', 'sensors.created_at')
            ->where('organizations.id', $id)
            ->orderBy('last_seen', 'desc')
            ->get();
            return response()->json([
                'code' => 201,
                'message' => 'success',
                'data' => $data
            ],201);
        } catch(Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
