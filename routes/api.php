<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\SettingController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/user-profile-update', [AuthController::class, 'profile_update']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    // User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/user-role', [UserController::class, 'role']);
        Route::post('/users', [UserController::class, 'store']);
        // Route::get('/users/{user}', [UserController::class, 'update']);
        Route::post('/updatedata/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);  //user delete
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::get('/userdetails/{id}', [UserController::class, 'userdetails']);  //get particular userdetails

        //role api------------------------
        Route::post('/role-create', [UserController::class, 'rolestore']);
        Route::post('/role-update/{id}', [UserController::class, 'roleedit']);
        Route::get('/role-delete/{id}', [UserController::class, 'roledelete']);
        Route::get('/role-details/{id}', [UserController::class, 'roledetails']);  //particular role details

        //language api-----------------------------
        Route::get('/languages', [LanguageController::class, 'index']);
        //Route::post('/language-create', [LanguageController::class, 'store']);
        // Route::post('/language-edit/{language}', [LanguageController::class, 'update']);
        Route::post('/language-add-edit/{id?}', [LanguageController::class, 'storeorupdate']);
        Route::get('/language-details/{id}', [LanguageController::class, 'languagedetails']);
        Route::get('/language-delete/{language}', [LanguageController::class, 'destroy']);

        //country api--------------------------
        Route::get('/countries', [CountryController::class, 'index']);
        //Route::post('/country-create', [CountryController::class, 'store']);
        //Route::post('/country-edit/{country}', [CountryController::class, 'update']);
        Route::post('/country-store-update/{id?}', [CountryController::class, 'countrystoreOrUpdate']);
        Route::get('/country-delete/{country}', [CountryController::class, 'destroy']);
        Route::get('/country-details/{country}', [CountryController::class, 'countrydetails']);

        //state api-------------------------------
        Route::get('/states', [CountryController::class, 'statelist']);
        // Route::post('/state-create', [CountryController::class, 'statestore']);
        //Route::post('/state-update/{id}', [CountryController::class, 'stateupdate']);
        Route::post('/state-store-update/{id?}', [CountryController::class, 'stateStoreOrUpdate']);
        Route::get('/state-details/{id}', [CountryController::class, 'statedetails']);
        Route::get('/state-delete/{id}', [CountryController::class, 'statedelete']);

        //city api-------------------------------
        Route::get('/cities', [CountryController::class, 'citylist']);
        Route::post('/city-store-update/{id?}', [CountryController::class, 'city_store_or_update']);
        Route::get('/city-details/{id}', [CountryController::class, 'citydetails']);
        Route::get('/city-delete/{id}', [CountryController::class, 'citydelete']);

        //brand api------------------------------------
        Route::get('/brandlist', [CategoryController::class, 'brandlist']);
        Route::post('/brand-store-update/{id?}', [CategoryController::class, 'brand_store_or_update']);
        Route::get('/branddetails/{id}', [CategoryController::class, 'branddetails']);
        Route::get('/branddelete/{id}', [CategoryController::class, 'branddelete']);

        //currency api---------------------------------
        Route::get('/currencylist', [CurrencyController::class, 'currencylist']);
        Route::post('/currency-store-update/{id?}', [CurrencyController::class, 'currency_store_or_update']);
        Route::get('/currencydetails/{id}', [CurrencyController::class, 'currencydetails']);
        Route::get('/currencydelete/{id}', [CurrencyController::class, 'currencydelete']);

        //paymentgateway api-------------------------------------
        Route::post('paymentgatway-store-update/{id?}', [CurrencyController::class, 'paymentgatway_store_or_update']);
        Route::get('paymentgatewaylist', [CurrencyController::class, 'paymentgatewaylist']);
        Route::get('paymentgatewaydetails/{id}', [CurrencyController::class, 'paymentgateway_details']);
        Route::get('paymentgatewaydelete/{id}', [CurrencyController::class, 'paymentgateway_delete']);

        //smsgateway api---------------------------
        Route::post('smsgatway-store-update/{id?}', [CurrencyController::class, 'smsgateway_store_or_update']);
        Route::get('smsgatewaylist', [CurrencyController::class, 'smsgatewaylist']);
        Route::get('smsgatewaydetails/{id}', [CurrencyController::class, 'smsgatewaydetails']);
        Route::get('smsgatewaydelete/{id}', [CurrencyController::class, 'smsgatewaydelete']);

        //bank api-----------------------------
        Route::post('bank-store-update/{id?}', [SettingController::class, 'bank_store_or_update']);
        Route::get('banklist', [SettingController::class, 'banklist']);
        Route::get('bankdetails/{id}', [SettingController::class, 'bankdetails']);
        Route::get('bankdelete/{id}', [SettingController::class, 'bankdelete']);
    });

    // Client routes
    Route::get('/clients', [ClientController::class, 'index']); // All authenticated
    Route::post('/clients', [ClientController::class, 'store']); // All authenticated
    Route::get('/clients/{client}', [ClientController::class, 'show']); // All authenticated

    // Manager & Admin can edit clients
    Route::middleware('role:admin,manager')->group(function () {
        Route::put('/clients/{client}', [ClientController::class, 'update']);
    });

    // Admin only - Delete clients
    Route::middleware('role:admin')->group(function () {
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    });

    // Lead routes
    Route::get('/leads', [LeadController::class, 'index']); // All authenticated
    Route::post('/leads', [LeadController::class, 'store']); // All authenticated
    Route::get('/leads/{lead}', [LeadController::class, 'show']); // All authenticated
    Route::put('/leads/{lead}', [LeadController::class, 'update']); // All authenticated

    // Manager can assign leads
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('/leads/{lead}/assign', [LeadController::class, 'assignLead']);
    });

    // Admin only - Delete leads
    Route::middleware('role:admin')->group(function () {
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
    });

    // Ticket routes
    Route::get('/tickets', [TicketController::class, 'index']); // All authenticated
    Route::post('/tickets', [TicketController::class, 'store']); // All authenticated
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']); // All authenticated
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']); // Support can update

    // Admin only - Delete tickets
    Route::middleware('role:admin')->group(function () {
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    });

    // Follow-up routes (Sales Executive)
    Route::get('/follow-ups', [FollowUpController::class, 'index']);
    Route::post('/follow-ups', [FollowUpController::class, 'store']);
    Route::get('/follow-ups/{followUp}', [FollowUpController::class, 'show']);
    Route::put('/follow-ups/{followUp}', [FollowUpController::class, 'update']);
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy']);
});
