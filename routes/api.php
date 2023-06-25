<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post("/event", "EventController@create");
    Route::post("/attend/{event}", "EventController@attend");
    Route::get("/user", "UserController@index");
});

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
