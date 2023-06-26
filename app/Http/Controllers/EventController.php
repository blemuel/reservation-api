<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * @OA\Post(
     *     path="/event",
     *     tags={"Events"},
     *     summary="Create a new event",
     *     @OA\RequestBody(
     *         description="Event data",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "eventDate", "location", "price", "attendeesLimit"},
     *            @OA\Property(property="title", type="string"),
     *            @OA\Property(property="description", type="string"),
     *           @OA\Property(property="eventDate", type="datetime"),
     *            @OA\Property(property="location", type="string"),
     *           @OA\Property(property="price", type="float"),
     *           @OA\Property(property="attendeesLimit", type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event registered successfully",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string"),
     *            @OA\Property(property="event", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     * )
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                "title" => "required|string|max:255",
                "description" => "required|string",
                "eventDate" => "required|date",
                "location" => "required|string",
                "price" => "required|numeric",
                "attendeesLimit" => "required|integer",
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

        $loggedUser = Auth::user();

        $event = Event::create([
            "title" => $request->title,
            "description" => $request->description,
            "eventDate" => $request->eventDate,
            "location" => $request->location,
            "price" => $request->price,
            "attendeesLimit" => $request->attendeesLimit,
            "user_id" => $loggedUser->id,
        ]);

        return response()->json(
            [
                "message" => "Event registered successfully",
                "event" => $event,
            ],
            201
        );
    }

    /**
     * @OA\Put(
     *      path="/event/{id}",
     *      tags={"Events"},
     *      summary="Update an event",
     *      @OA\Parameter(
     *          description="ID of event to update",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *               )
     *      ),
     *      @OA\RequestBody(
     *          description="Event data",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title", "description", "eventDate", "location", "price", "attendeesLimit"},
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="eventDate", type="datetime"),
     *              @OA\Property(property="location", type="string"),
     *              @OA\Property(property="price", type="float"),
     *              @OA\Property(property="attendeesLimit", type="integer"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Event updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="event", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation failed",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     * )
     *
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                "title" => "sometimes|required|string|max:255",
                "description" => "sometimes|required|string",
                "eventDate" => "sometimes|required|date",
                "location" => "sometimes|required|string",
                "price" => "sometimes|required|numeric",
                "attendeesLimit" => "sometimes|required|integer",
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

        $loggedUser = Auth::user();
        $event = Event::where("user_id", $loggedUser->id)->findOrFail($id);

        $event->fill(
            $request->only(
                "title",
                "description",
                "eventDate",
                "location",
                "price",
                "attendeesLimit"
            )
        );

        $event->save();

        return response()->json(
            [
                "message" => "Event updated successfully",
                "event" => $event,
            ],
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/events",
     *     summary="Get events",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date filter (yyyy-mm-dd)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date filter (yyyy-mm-dd)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="eventDate", type="datetime"),
     *                  @OA\Property(property="location", type="string"),
     *                  @OA\Property(property="price", type="float"),
     *                  @OA\Property(property="attendeesLimit", type="integer"),
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="created_at", type="datetime"),
     *                  @OA\Property(property="updated_at", type="datetime"),
     *                  @OA\Property(property="deleted_at", type="datetime"),
     *            )
     *        )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function getEvents(Request $request)
    {
        $query = Event::query();

        if ($request->filled("from")) {
            $from = $request->input("from");
            $query->where("eventDate", ">=", $from);
        }

        if ($request->filled("to")) {
            $to = $request->input("to");
            $query->where("eventDate", "<=", $to);
        }

        $events = $query->get();

        return response()->json($events);
    }

    /**
     * @OA\Get(
     *     path="/event/{id}",
     *     summary="Get event by ID with attendees",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *                 @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="eventDate", type="datetime"),
     *                  @OA\Property(property="location", type="string"),
     *                  @OA\Property(property="price", type="float"),
     *                  @OA\Property(property="attendeesLimit", type="integer"),
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="created_at", type="datetime"),
     *                  @OA\Property(property="updated_at", type="datetime"),
     *                  @OA\Property(property="deleted_at", type="datetime"),
     *                 @OA\Property(
     *                           property="reservations",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="event_id", type="integer"),
     *                          @OA\Property(property="user_id", type="integer"),
     *                          @OA\Property(property="created_at", type="datetime"),
     *                          @OA\Property(property="updated_at", type="datetime"),
     *                          @OA\Property(property="deleted_at", type="datetime"),
     *                          )
     *                  )
     *           )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */

    public function getEvent(Request $request, $id)
    {
        $event = Event::with("reservations")->findOrFail($id);

        return response()->json($event);
    }

    /**
     * @OA\Get(
     *     path="/events/user",
     *     summary="Get events for the logged-in user",
     *     tags={"Events"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="eventDate", type="datetime"),
     *                  @OA\Property(property="location", type="string"),
     *                  @OA\Property(property="price", type="float"),
     *                  @OA\Property(property="attendeesLimit", type="integer"),
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="created_at", type="datetime"),
     *                  @OA\Property(property="updated_at", type="datetime"),
     *                  @OA\Property(property="deleted_at", type="datetime"),
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date filter (yyyy-mm-dd)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date filter (yyyy-mm-dd)",
     *         @OA\Schema(type="string", format="date")
     *     )
     * )
     */

    public function getUserEvents(Request $request)
    {
        $loggedUser = Auth::user();
        $query = Event::query()->where("user_id", $loggedUser->id);

        if ($request->filled("from")) {
            $from = $request->input("from");
            $query->where("eventDate", ">=", $from);
        }

        if ($request->filled("to")) {
            $to = $request->input("to");
            $query->where("eventDate", "<=", $to);
        }

        $events = $query->get();

        return response()->json($events);
    }
}
