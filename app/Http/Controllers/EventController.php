<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Event;

class EventController extends Controller
{
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

    public function getEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        return response()->json($event);
    }

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
