<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function () {
    return view('upload');
});

Route::post('/extract-certificate-serial-number', [CertificateController::class, 'extractCertificateSerialNumber']);
// routes/web.php

Route::post('/upload/pdf', [PdfController::class, 'extractDetails'])->name('upload.pdf');





