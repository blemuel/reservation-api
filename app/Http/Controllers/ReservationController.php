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
    /**
     * @OA\Post(
     *     path="/reservation",
     *     summary="Create a reservation",
     *     tags={"Reservations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", description="Event ID"),
     *             @OA\Property(property="numberOfTickets", type="integer", description="Number of tickets")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Reservation registered successfully"),
     *             @OA\Property(property="registration", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"event_id": {"The event_id field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event already happened"),
     *             @OA\Property(property="messages", type="string", example="Not enough tickets available")
     *         )
     *     )
     * )
     */

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

        if (strtotime($event->eventDate) < strtotime("now")) {
            return response()->json(
                [
                    "message" => "Event already happened",
                ],
                422
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

    /**
     * @OA\Get(
     *     path="/reservations/user",
     *     summary="Get user reservations",
     *     tags={"Reservations"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Reservation ID"),
     *                  @OA\Property(property="event_id", type="integer", description="Event ID"),
     *                  @OA\Property(property="user_id", type="integer", description="User ID"),
     *                  @OA\Property(property="numberOfTickets", type="integer", description="Number of tickets"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", description="Created at"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", description="Updated at"),
     *                  @OA\Property(property="event", type="object", description="Event",
     *                      @OA\Property(property="id", type="integer", description="Event ID"),
     *                      @OA\Property(property="name", type="string", description="Event name"),
     *                      @OA\Property(property="description", type="string", description="Event description"),
     *                      @OA\Property(property="eventDate", type="string", format="date-time", description="Event date"),
     *                      @OA\Property(property="attendeesLimit", type="integer", description="Event attendees limit"),
     *                      @OA\Property(property="created_at", type="string", format="date-time", description="Created at"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time", description="Updated at")
     *                      )
     *                 )
     *            )
     *        )
     *    ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function getUserReservations(Request $request)
    {
        $loggedUser = Auth::user();

        $reservations = Reservation::where("user_id", $loggedUser->id)
            ->with("event")
            ->get();

        return response()->json($reservations);
    }
}
