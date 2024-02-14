<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SponsorController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\HighlightController;
use App\Http\Controllers\API\SpeakerController;
use App\Http\Controllers\API\AgendaController;
use App\Http\Controllers\API\AttendDetailController;
use App\Http\Controllers\API\AttendWhoDetailController;
use App\Http\Controllers\API\AttendWhyDetailController;
use App\Http\Controllers\API\ThemeController;

//Test
Route::get('/test', [AuthController::class, 'test']);

//Auth - Register
Route::post('/register', [AuthController::class, 'register']);

//Auth - Login
Route::post('/login', [AuthController::class, 'login']);

//Auth - Forget password 
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

//Auth - Reset password
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

//Banners
Route::get('/all-banners/{event_id}', [BannerController::class, 'all_banners']);
Route::get('/banners', [BannerController::class, 'index']);
Route::post('/banners', [BannerController::class, 'store']);
Route::get('/banners/{id}', [BannerController::class, 'show']);
Route::put('/banners/{id}', [BannerController::class, 'update']);
Route::delete('/banners/{id}', [bannerController::class, 'destroy']);

//Highlights
Route::get('/all-highlights/{event_id}', [HighlightController::class, 'all_highlights']);
Route::get('/highlights', [HighlightController::class, 'index']);
Route::post('/highlights', [HighlightController::class, 'store']);
Route::get('/highlights/{id}', [HighlightController::class, 'show']);
Route::put('/highlights/{id}', [HighlightController::class, 'update']);
Route::delete('/highlights/{id}', [HighlightController::class, 'destroy']);

//Sponsors
Route::get('/all-sponsors/{event_id}', [SponsorController::class, 'all_sponsors']);
Route::get('/sponsors', [SponsorController::class, 'index']);
Route::post('/sponsors', [SponsorController::class, 'store']);
Route::get('/sponsors/{id}', [SponsorController::class, 'show']);
Route::put('/sponsors/{id}', [SponsorController::class, 'update']);
Route::delete('/sponsors/{id}', [SponsorController::class, 'destroy']);

//Who Attends
Route::get('/all-attends/{event_id}', [AttendWhoDetailController::class, 'all_attends']);
Route::get('/attends', [AttendWhoDetailController::class, 'index']);
Route::post('/attends', [AttendWhoDetailController::class, 'store']);
Route::get('/attends/{id}', [AttendWhoDetailController::class, 'show']);
Route::put('/attends/{id}', [AttendWhoDetailController::class, 'update']);
Route::delete('/attends/{id}', [AttendWhoDetailController::class, 'destroy']);

//Why Attends
Route::get('/all-why-attends/{event_id}', [AttendWhyDetailController::class, 'all_attends']);
Route::get('/why-attends', [AttendWhyDetailController::class, 'index']);
Route::post('/why-attends', [AttendWhyDetailController::class, 'store']);
Route::get('/why-attends/{id}', [AttendWhyDetailController::class, 'show']);
Route::put('/why-attends/{id}', [AttendWhyDetailController::class, 'update']);
Route::delete('/why-attends/{id}', [AttendWhyDetailController::class, 'destroy']);

//Agendas
Route::get('/all-agendas/{event_id}', [AgendaController::class, 'all_agendas']);
Route::get('/agendas', [AgendaController::class, 'index']);
Route::post('/agendas', [AgendaController::class, 'store']);
Route::get('/agendas/{id}', [AgendaController::class, 'show']);
Route::put('/agendas/{id}', [AgendaController::class, 'update']);
Route::delete('/agendas/{id}', [AgendaController::class, 'destroy']);

//Speakers
Route::get('/all-speakers/{event_id}', [SpeakerController::class, 'all_speakers']);
Route::get('/speakers', [SpeakerController::class, 'index']);
Route::post('/speakers', [SpeakerController::class, 'store']);
Route::get('/speakers/{id}', [SpeakerController::class, 'show']);
Route::put('/speakers/{id}', [SpeakerController::class, 'update']);
Route::delete('/speakers/{id}', [SpeakerController::class, 'destroy']);

//Themes
Route::get('/all-themes/{event_id}', [ThemeController::class, 'all_themes']);
Route::get('/themes', [ThemeController::class, 'index']);
Route::post('/themes', [ThemeController::class, 'store']);
Route::get('/themes/{id}', [ThemeController::class, 'show']);
Route::put('/themes/{id}', [ThemeController::class, 'update']);
Route::delete('/themes/{id}', [ThemeController::class, 'destroy']);

//Events
Route::get('/show_event/{slug}', [EventController::class, 'showEventBySlug']);
Route::get('/display-events/{id}', [EventController::class, 'display']);
Route::get('/events', [EventController::class, 'index']);
Route::post('/events', [EventController::class, 'store']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::put('/events/{id}', [EventController::class, 'update']);
Route::delete('/events/{id}', [EventController::class, 'destroy']);


//Titles
Route::get('/titles', [SpeakerController::class, 'titles']);


Route::get('/countries', [AuthController::class, 'countries']);
Route::get('/states', [AuthController::class, 'states']);
Route::get('/cities', [AuthController::class, 'cities']);
Route::get('/jobtitles', [AuthController::class, 'jobtitles']);
Route::get('/companies', [AuthController::class, 'companies']);
Route::get('/industries', [AuthController::class, 'industries']);
Route::get('/employee-size', [AuthController::class, 'employeeSize']);

Route::get('/send-sms', [AuthController::class, 'sendsms']);

//Protecting Routes
Route::middleware('auth:sanctum')->group(function () {

  //Check Authentication
  Route::get('/checkingAuthenticated', function () {
    return response()->json(['message' => 'You are in Klout Marketing Club', 'status' => 200], 200);
  });

  //Get user details
  Route::get('/profile', [UserController::class, 'profile']);

  //Update Profile
  Route::post('/updateprofile', [UserController::class, 'updateprofile']);

  //Change Password
  Route::post('/changepassword', [UserController::class, 'changePassword']);

  //Reports -
  Route::get('/reports', [ReportController::class, 'reports']);
  Route::post('/event-report', [ReportController::class, 'generateCSV']);
  Route::get('/event-report-download/{id}', [ReportController::class, 'downloadCSV']);
  Route::delete('/reports/{id}', [ReportController::class, 'destroy']);

  //Logout 
  Route::post('logout', [AuthController::class, 'logout']);
});
