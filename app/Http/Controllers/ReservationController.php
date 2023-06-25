<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                "event_id" => "required|numeric",
                "numberOfTickets" => "required|numeric",
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                422
            );
        }

        $event = Event::where("id", $request->event_id)->first();

        if (!$event->exists()) {
            return response()->json(
                [
                    "message" => "Event not found",
                ],
                404
            );
        }

        $eventReservations = Reservation::where(
            "event_id",
            $request->event_id
        )->sum("numberOfTickets");

        if (
            $eventReservations + $request->numberOfTickets >
            $event->attendeesLimit
        ) {
            return response()->json(
                [
                    "message" => "Not enough tickets available",
                ],
                422
            );
        }

        $loggedUser = Auth::user();

        $reservation = Reservation::create([
            "event_id" => $request->event_id,
            "user_id" => $loggedUser->id,
            "numberOfTickets" => $request->numberOfTickets,
        ]);

        return response()->json(
            [
                "message" => "Reservation registered successfully",
                "registration" => $reservation,
            ],
            201
        );
    }

    public function getUserReservations(Request $request)
    {
        $loggedUser = Auth::user();

        $reservations = Reservation::where("user_id", $loggedUser->id)
            ->with("event")
            ->get();

        return response()->json($reservations);
    }
}
