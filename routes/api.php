<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PaymentController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/', function () {
    $routes = Route::getRoutes();
    echo '
        <table style="width: 100%; border-collapse: collapse;" border="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>URI</th>
                </tr>
            </thead>
            <tbody>
    ';
    $i = 1;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'api/')) {
            echo "
                <tr>
                    <td>{$i}</td>
                    <td>"
                        . $route->methods()[0] .
                        " - <a href='" . env('APP_URL') . $route->uri() . "'>"
                        . env('APP_URL') . $route->uri()
                        . "</a>
                    </td>
                </tr>
            ";
            $i++; 
        };
    }
    echo '
            </tbody>
        </table>
    ';
    return "";
});


Route::apiResource('/lead',LeadController::class)->only('index', 'store', 'show', 'update', 'destroy');
Route::get('faq-list', [FaqController::class,'index']);
Route::get('review-list', [ReviewController::class,'index']);

Route::post('pay',[PaymentController::class, 'pay']);

Route::post('intent/payment',[PaymentController::class, 'createPaymentIntent']);
Route::post('intent/subscription',[PaymentController::class, 'createSubscriptionStripe']);
Route::post('/success', [PaymentController::class, 'success']);





Route::post('cancel',[PaymentController::class, 'cancel'])->name('cancel');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
