<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingForDriverController extends Controller
{
    public function pendingList(Request $request)
    {
        $ambulance = $request->user()->ambulance;
        $ambulanceLat = $ambulance->latitude;
        $ambulanceLng = $ambulance->longitude;

        $bookings = Booking::where('status', 'pending')
            ->whereRaw(
                "6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))) <= ?",
                [$ambulanceLat, $ambulanceLng, $ambulanceLat, 20]
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Booking retrieved successfully',
            'data' => $bookings
        ], 200);
    }
    public function index(Request $request)
    {
        $bookings = Booking::where('status', ['accepted', 'picked', 'completed'])
            ->where('ambulance_id', $request->user()->ambulance->id)
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Booking retrieved successfully',
            'data' => $bookings
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->status == 'pending') {
            $booking->update([
                'ambulance_id' => $request->user()->ambulance->id,
                'status' => 'accepted',
            ]);
        } else if ($booking->status == 'accepted') {
            if ($booking->ambulance->id !== $request->user()->ambulance->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to pick this booking',
                ], 403);
            }

            $booking->update([
                'arrival_time' => now(),
                'status' => 'picked',
            ]);
        } else if ($booking->status == 'picked') {
            if ($booking->ambulance->id !== $request->user()->ambulance->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to complete this booking',
                ], 403);
            }
            $booking->update([
                'completion_time' => now(),
                'status' => 'completed',
            ]);
        } else if ($booking->status == 'canceled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking has been canceled',
            ], 403);
        } else if ($booking->status == 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking has been completed',
            ], 403);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Booking updated successfully',
            'data' => $booking
        ], 200);
    }
}
