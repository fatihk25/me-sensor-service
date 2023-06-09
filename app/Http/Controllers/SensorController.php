<?php

namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class SensorController extends Controller
{
    //
     public function __construct()
    {
        $this->middleware('auth:sensors-api', ['only' => ['updateStatus']]);
    }

    public function detail($id) {
        try {
            $data = DB::table('sensors')
            ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
            ->select('sensors.*', 'organizations.id as organization_id', 'organizations.name as organization_name', 'organizations.oinkcode')
            ->where('sensors.id', $id)
            ->first();
            return response()->json([
                'code ' => 201,
                'message' => 'success',
                'data' => $data
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required'],
            'organization_id' => ['required'],
            'protected_subnet' => ['required'],
            'external_subnet' => ['required'],
            'mqtt_topic' => ['required'],
            'mqtt_ip' => ['required'],
            'mqtt_port' => ['required'],
            'network_interface' => ['required']
        ]);

        try {
            $sensor = Sensor::create([
                'name' => $validatedData['name'],
                'organization_id' => $validatedData['organization_id'],
                'protected_subnet' => $validatedData['protected_subnet'],
                'external_subnet' => $validatedData['external_subnet'],
                'mqtt_topic' => $validatedData['mqtt_topic'],
                'mqtt_ip' => $validatedData['mqtt_ip'],
                'mqtt_port' => $validatedData['mqtt_port'],
                'network_interface' => $validatedData['network_interface'],
                'status' => 'created',
                'uuid' => Str::uuid(),
            ]);

            return response()->json([
                'code ' => 201,
                'message' => 'registered',
                'data' => new SensorResource($sensor)
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function login(Request $request) {
        try {
            $request->validate([
                'uuid' => 'required'
            ]);

            $credential = $request->input('uuid');
            $data = Sensor::where('uuid', $credential)->first();
            $token = auth('sensors-api')->login($data);
            $data = DB::table('sensors')
                ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
                ->select('sensors.*', 'organizations.oinkcode')
                ->where('organizations.id', $data->organization_id)
                ->where('sensors.uuid', $credential)->first();

            if(!$token ) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $this->respondWithToken($token, $data);

        }catch(\Exception $e){
            return response()->json([
                'code' => 401,
                'message' => $e->getMessage()
            ],401);
        }
    }

    public function edit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['string'],
            'organization_id' => ['integer'],
            'protected_subnet' => ['string'],
            'external_subnet' => ['string'],
            'mqtt_topic' => ['string'],
            'mqtt_ip' => ['string'],
            'mqtt_port' => ['string'],
            'network_interface' => ['string'],
            'status' => ['string'],
        ]);

        try {
            $sensor = Sensor::findOrfail($id);

            $sensor->update([
                'name' => $validatedData['name'] ?? $sensor->name,
                'organization_id' => $validatedData['organization_id'] ?? $sensor->organization_id,
                'protected_subnet' => $validatedData['protected_subnet'] ?? $sensor->protected_subnet,
                'external_subnet' => $validatedData['external_subnet'] ?? $sensor->external_subnet,
                'mqtt_topic' => $validatedData['mqtt_topic'] ?? $sensor->mqtt_topic,
                'mqtt_ip' => $validatedData['mqtt_ip'] ?? $sensor->mqtt_ip,
                'mqtt_port' => $validatedData['mqtt_port'] ?? $sensor->mqtt_port,
                'network_interface' => $validatedData['network_interface'] ?? $sensor->network_interface,
            ]);

            $sensor->status = "update on progress";
            $sensor->save();

            return response()->json([
                'code ' => 201,
                'message' => 'updated',
                'data' => new SensorResource($sensor)
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'update failed',
            ], 403);
        }
    }

    public function updateStatus(Request $request, $id) {
        $validatedData = $request->validate([
            'status' => ['string']
        ]);

        try {
            $sensor = Sensor::findOrfail($id);
            $sensor->update([
                'status' => $validatedData['status'] ?? $sensor->status,
            ]);

            return response()->json([
                'code ' => 201,
                'message' => 'updated',
                'data' => new SensorResource($sensor)
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'update failed',
            ], 403);
        }
    }

    public function delete($id) {
        try {
            $sensor = Sensor::findOrfail($id);
            $sensor->status = "deleted";
            $sensor->save();

            return response()->json([
                'code ' => 201,
                'message' => $sensor,
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'delete failed',
            ], 403);
        }
    }

    // public function getSensorCounts($id)
    // {
    //     $activeStatus = 'active';

    //     $activeSensorCount = DB::table('sensors')
    //         ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
    //         ->select(DB::raw('count(*) as count'))
    //         ->where('organizations.id', $id)
    //         ->where('sensors.status', $activeStatus)
    //         ->first();

    //     $nonActiveSensorCount = DB::table('sensors')
    //         ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
    //         ->select(DB::raw('count(*) as count'))
    //         ->where('organizations.id', $id)
    //         ->where('sensors.status', '!=', $activeStatus)
    //         ->first();

    //     $sensorCounts = [
    //         'active' => $activeSensorCount->count ?? 0,
    //         'nonActive' => $nonActiveSensorCount->count ?? 0
    //     ];

    //     return response()->json($sensorCounts);
    // }


    public function getSensorCounts($id)
    {
        $activeSensorCount = DB::table('sensors')
            ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
            ->leftJoin('sensor_heartbeats', function ($join) {
                $join->on('sensors.id', '=', 'sensor_heartbeats.sensor_id')
                    ->where('sensor_heartbeats.id', function ($query) {
                        $query->select('id')
                            ->from('sensor_heartbeats')
                            ->whereColumn('sensor_heartbeats.sensor_id', 'sensors.id')
                            ->latest('last_seen')
                            ->limit(1);
                    });
            })
            ->select(DB::raw('count(*) as count'))
            ->where('organizations.id', $id)
            ->where('sensors.status', '!=', 'deleted')
            ->where('sensor_heartbeats.isActive', true)
            ->first();

        $nonActiveSensorCount = DB::table('sensors')
            ->join('organizations', 'sensors.organization_id', '=', 'organizations.id')
            ->leftJoin('sensor_heartbeats', function ($join) {
                $join->on('sensors.id', '=', 'sensor_heartbeats.sensor_id')
                    ->where('sensor_heartbeats.id', function ($query) {
                        $query->select('id')
                            ->from('sensor_heartbeats')
                            ->whereColumn('sensor_heartbeats.sensor_id', 'sensors.id')
                            ->latest('last_seen')
                            ->limit(1);
                    });
            })
            ->select(DB::raw('count(*) as count'))
            ->where('organizations.id', $id)
            ->where('sensors.status', '!=', 'deleted')
            ->where('sensor_heartbeats.isActive', false)
            // ->orWhereNull('sensor_heartbeats.isActive')
            ->first();
            // dd($nonActiveSensorCount);

        $sensorCounts = [
            'active' => $activeSensorCount->count ?? 0,
            'nonActive' => $nonActiveSensorCount->count ?? 0
        ];

        return response()->json($sensorCounts);
    }


         /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token,$data)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('sensors-api')->factory()->getTTL() * (60 * 24 * 7),
            'data' => $data
        ]);
    }

    public function refresh() {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token, true);
        return response()->json([
            'code' => 200,
            'access_token' => $newToken
        ], 200);
    }
}
