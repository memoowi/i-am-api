<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends Controller
{
    public function index(Request $request) // index for user
    {
        $bookings = $request->user()->bookings()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings,
        ], 200);
    }
    public function show($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking retrieved successfully',
            'data' => $booking,
        ], 200);
    }
    public function store(Request $request) // can only make one booking at a time
    {
        try {
            $request->validate([
                'description' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'booking_time' => 'required|date_format:Y-m-d H:i:s',
            ]);

            if ($request->user()->bookings()->whereIn('status', ['pending', 'accepted', 'picked'])->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only make one booking at a time',
                ], 403);
            }

            $booking = $request->user()->bookings()->create([
                'description' => $request->description,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'booking_time' => $request->booking_time,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => $booking,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking',
                'errors' => $e->errors(),
            ], 422); // HTTP Unprocessable Entity
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking',
                'error' => env('APP_DEBUG') === true ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function update(Request $request, $id) // update for user (CANCEL BOOKING on pending only)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to cancel this booking',
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only cancel pending bookings',
            ], 403);
        }

        $booking->update([
            'status' => 'canceled',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking canceled successfully',
            'data' => $booking,
        ]);
    }
}
