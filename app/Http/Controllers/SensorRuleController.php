<?php

namespace App\Http\Controllers;

use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use App\Models\SensorRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SensorRuleController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth:sensors-api');
    // }

    public function upload(Request $request, $id) {
        // dd($request);
        $sensor = Sensor::find($id);
        if (!$sensor) {
            return response()->json(['error' => 'sensor not found'], 404);
        }

        $content = $request->file('rules');
        // dd(file_get_content($content));

        if ($request->hasFile('rules')) {
            $rulesFile = $request->file('rules');
            $extension = $rulesFile->getClientOriginalExtension();
            $filename = $sensor->uuid . '.' . $extension;
            $folderPath = 'sensor_rules/' . $sensor->uuid;
            $data = SensorRule::updateOrCreate(
                ['sensor_uuid' => $sensor->uuid],
                ['file' => $folderPath]
            );

            $rulesFile->storeAs($folderPath, $filename, 'public');

            return response()->json([
                'message' => 'Rules uploaded',
                'data' => $data
            ], 200);
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }
}
