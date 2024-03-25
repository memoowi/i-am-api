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
            if ($request->user()->ambulance()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ambulances already exists',
                ], 400);
            }
            $ambulances = $request->user()->ambulance()->create([
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
                'license_plate' => 'string',
                'model' => 'string',
                'latitude' => 'numeric',
                'longitude' => 'numeric',
            ]);
            $request->user()->ambulance()->update([
                'license_plate' => $request->license_plate ?? $request->user()->ambulance->license_plate,
                'model' => $request->model ?? $request->user()->ambulance->model,
                'latitude' => $request->latitude ?? $request->user()->ambulance->latitude,
                'longitude' => $request->longitude ?? $request->user()->ambulance->longitude,
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
