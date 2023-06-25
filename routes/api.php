<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post("/event", [EventController::class, "create"]);
    Route::put("/event/{id}", [EventController::class, "update"]);
    Route::get("/events", [EventController::class, "getEvents"]);
    Route::get("/event/{id}", [EventController::class, "getEvent"]);
    Route::get("/events/user", [EventController::class, "getUserEvents"]);

    Route::get("/reservations/{id}", [
        ReservationController::class,
        "getEventReservations",
    ]);
    Route::get("/reservations/user/{id}", [
        ReservationController::class,
        "getUserReservations",
    ]);
    Route::post("/reservation/{id}", [ReservationController::class, "attend"]);
});

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
