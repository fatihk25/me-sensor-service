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
        $sensor = Sensor::find($id);
        if (!$sensor) {
            return response()->json(['error' => 'sensor not found'], 404);
        }

        if ($request->hasFile('file')) {
            $rulesFile = $request->file('file');
            $extension = $rulesFile->getClientOriginalExtension();
            $filename = $sensor->uuid . '.' . $extension;
            $folderPath = 'sensor_rules/' . $sensor->uuid;
            $data = SensorRule::updateOrCreate(
                ['sensor_uuid' => $sensor->uuid],
                ['file' => $folderPath . '/' . $filename]
            );

            $rulesFile->storeAs($folderPath, $filename, 'public');
            $sensor->status = "rules uploaded";
            $sensor->save();

            return response()->json([
                'message' => 'Rules uploaded',
                'data' => $data
            ], 200);
        }

        return response()->json(['error' => $request->all()], 400);
    }
}
