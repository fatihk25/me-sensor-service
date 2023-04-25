<?php

namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class SensorController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth:sensors-api', ['except' => ['login', 'register', '']]);
    // }

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
            'network_interface' => ['required'],
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
            'name' => ['required'], 
            'organization_id' => ['required'],
            'protected_subnet' => ['required'],
            'external_subnet' => ['required'],
            'mqtt_topic' => ['required'],
            'mqtt_ip' => ['required'],
            'mqtt_port' => ['required'],
            'network_interface' => ['required'],
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
            $sensor->delete();

            return response()->json([
                'code ' => 201,
                'message' => 'deleted',
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'delete failed',
            ], 403);
        }
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
            'expires_in' => auth('sensors-api')->factory()->getTTL() * 60,
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
