<?php

namespace App\Http\Controllers;

use App\Models\Ambulance;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AmbulanceController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Ambulances retrieved successfully',
            'data' => Ambulance::all()->load(['user']),
        ], 200);
    }
    public function show($id)
    {
        $ambulances = Ambulance::find($id);
        if ($ambulances) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ambulances retrieved successfully',
                'data' => $ambulances,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Ambulances not found',
            ], 404);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'license_plate' => 'required',
                'model' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            ]);
            $ambulances = $request->user()->ambulance()->updateOrCreate([
                'license_plate' => $request->license_plate,
                'model' => $request->model,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Ambulances created successfully',
                'data' => $ambulances,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ambulances creation failed',
                'error_details' => $e->errors(),
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ambulances creation failed',
                'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $request->validate([
                'license_plate' => 'required',
                'model' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            ]);
            $request->user()->ambulance()->update([
                'license_plate' => $request->license_plate,
                'model' => $request->model,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Ambulances updated successfully',
                'data' => Ambulance::find($request->user()->ambulance->id),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ambulances update failed',
                'error_details' => $e->errors(),
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ambulances update failed',
                'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null,
            ], 500);
        }
    }
}
