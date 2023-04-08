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
    public function __construct()
    {
        $this->middleware('auth:sensors-api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $request->validate([
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
            $data = new Sensor;
            $data->name = $request->input('name');
            $data->organization_id = $request->input('organization_id');
            $data->protected_subnet = $request->input('protected_subnet');
            $data->external_subnet = $request->input('external_subnet');
            $data->mqtt_topic = $request->input('mqtt_topic');
            $data->mqtt_ip = $request->input('mqtt_ip');
            $data->mqtt_port = $request->input('mqtt_port');
            $data->network_interface = $request->input('network_interface');
            $data->uuid = Str::uuid();
            $data->save();

            return response()->json([
                'code ' => 201,
                'message' => 'registered',
                'data' => new SensorResource($data)
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
            // dd($data);
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
        $request->validate([
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
            $data = Sensor::find($id);
            $data->name = $request->input('name');
            $data->organization_id = $request->input('organization_id');
            $data->protected_subnet = $request->input('protected_subnet');
            $data->external_subnet = $request->input('external_subnet');
            $data->mqtt_topic = $request->input('mqtt_topic');
            $data->mqtt_ip = $request->input('mqtt_ip');
            $data->mqtt_port = $request->input('mqtt_port');
            $data->network_interface = $request->input('network_interface');
            $data->save();

            return response()->json([
                'code ' => 201,
                'message' => 'updated',
                'data' => new SensorResource($data)
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'update failed',
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
