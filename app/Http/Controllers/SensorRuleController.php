<?php

namespace App\Http\Controllers;

use App\Models\SensorRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SensorRuleController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sensors-api');
    }

    public function update(Request $request) {
        try {
            $data = SensorRule::where('sensor_uuid', $request->input('sensor_uuid'))->first();
            if ($data) {
                $data->name = $request->input('name');
                $data->sensor_uuid = $request->input('sensor_uuid');
                $data->file = Str::random(10);
                $data->status = $request->input('status');
                $data->save();
                return response()->json([
                    'code ' => 201,
                    'message' => 'updated',
                    'data' => $data
                ],201);
            } else {
                $data = new SensorRule;
                $data->name = $request->input('name');
                $data->sensor_uuid = $request->input('sensor_uuid');
                $data->file = Str::random(10);
                $data->status = $request->input('status');
                $data->save();
                return response()->json([
                    'code ' => 201,
                    'message' => 'created',
                    'data' => $data
                ],201);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
