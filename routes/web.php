<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';



// Route::get('/send-test-email', function () {
//     Mail::raw('This is a test email from Laravel using SendGrid!', function ($message) {
//         $message->to('your-email@example.com') // Replace with your email
//             ->subject('Test Email from SendGrid');
//     });

//     return response()->json(['message' => 'Email sent successfully']);
// });