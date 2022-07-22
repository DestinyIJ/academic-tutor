<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ParentRequestController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCredentialController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------


| To authenticate your SPA, your SPA's "login" page should first make a request 
| to the /sanctum/csrf-cookie endpoint to initialize CSRF protection for the application:
|
| axios.get('/sanctum/csrf-cookie').then(response => {
|    axios.post('/api/user/login')
| });
|
| During this request, Laravel will set an XSRF-TOKEN cookie containing the current CSRF token.
| This token should then be passed in an X-XSRF-TOKEN header on subsequent requests,
| which some HTTP client libraries like Axios and the Angular HttpClient will do automatically for you.
| If your JavaScript HTTP library does not set the value for you, 
| you will need to manually set the X-XSRF-TOKEN header to match 
| the value of the XSRF-TOKEN cookie that is set by this route.
|
*/


Route::post('/user/register', [UserController::class, 'store'])->middleware('guest'); // === route to signup
 // route to login
Route::get('/user/exist/{email}', [UserController::class, 'existEmail'])->middleware('guest'); // Check if User exist
Route::get('/user/exist/{phone}', [UserController::class, 'existPhone'])->middleware('guest');

Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => 'user'
    ], function () { 
        Route::get('', [UserController::class, 'showUser']); // route to get current authenticated user 
        Route::post('/update/{id}', [UserController::class, 'update']); // route to update by id

        /* download-file/hello@xample/_profile_picture.jpg 
        *  from database,  filename = hello@xample/_profile_picture.jpg
        * just add filename to route, /download-file/{filename} 
        */
        Route::get('/download-file/{email}/{name}', [UserController::class, 'downloadFile'] );
        Route::get('/get-file-url/{email}/{name}', [UserController::class, 'getFileUrl'] ); 
    }
);

Route::group([
    'middleware' => ['auth:sanctum', 'is.admin'],
    'prefix' => 'admin'
    ], function () {
	    Route::get('/get-user/{id}', [UserController::class, 'showOne']); // route to get user by id 
	    Route::get('/get-users', [UserController::class, 'showAll']); // route to get all users 
        Route::post('delete-user/{id}', [UserController::class, 'destroy']); //route to delete user

        Route::get('/requests', [ParentRequestController::class, 'showAll']); // retrieve all request made by parents
        Route::get('/requests/parent/{parent-id}', [ParentRequestController::class, 'ShowRequestsByParent']); // retrieve all requests made by a parent
        Route::get('/requests/teacher/{teacher-id}', [ParentRequestController::class, 'ShowRequestsForTeacher']); // retrieve all requests made for a teacher
        
        Route::get('/credentials/{id}', [TeacherCredentialController::class, 'showCredentials']); //route to get all teacher credentials details

        // below are route to verify teacher credentials
        Route::post('/verify-teacher/right-to-work/{id}', [TeacherCredentialController::class, 'right_to_work']);
        Route::post('/verify-teacher/dbs-certificate/{id}', [TeacherCredentialController::class, 'dbs_certificate']);
        Route::post('/verify-teacher/educational-qualification/{id}', [TeacherCredentialController::class, 'educational_qualification']);
        Route::post('/verify-teacher/qts/{id}', [TeacherCredentialController::class, 'qts']);
        Route::post('/verify-teacher/passport-or-driver-license/{id}', [TeacherCredentialController::class, 'passport_id_or_driver_license']);
        Route::post('/verify-teacher/passport-photo/{id}', [TeacherCredentialController::class, 'passport_photo']);
        Route::post('/verify-teacher/proof-of-address/{id}', [TeacherCredentialController::class, 'proof_of_address']);
        Route::post('/verify-teacher/national-insurance-number/{id}', [TeacherCredentialController::class, 'national_insurance_number']);
        Route::post('/verify-teacher/permit-or-id/{id}', [TeacherCredentialController::class, 'permit_or_id']);

        // below are route to unverify teacher credentials
        Route::post('/unverify-teacher/right-to-work/{id}', [TeacherCredentialController::class, 'not_right_to_work']); 
        Route::post('/unverify-teacher/dbs-certificate/{id}', [TeacherCredentialController::class, 'not_dbs_certificate']);
        Route::post('/unverify-teacher/educational-qualification/{id}', [TeacherCredentialController::class, 'not_educational_qualification']);
        Route::post('/unverify-teacher/qts/{id}', [TeacherCredentialController::class, 'not_qts']);
        Route::post('/unverify-teacher/passport-or-driver-license/{id}', [TeacherCredentialController::class, 'not_passport_id_or_driver_license']);
        Route::post('/unverify-teacher/passport-photo/{id}', [TeacherCredentialController::class, 'not_passport_photo']);
        Route::post('/unverify-teacher/proof-of-address/{id}', [TeacherCredentialController::class, 'not_proof_of_address']);
        Route::post('/unverify-teacher/national-insurance-number/{id}', [TeacherCredentialController::class, 'not_national_insurance_number']);
        Route::post('/unverify-teacher/permit-or-id/{id}', [TeacherCredentialController::class, 'not_permit_or_id']);
    }
);


Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => 'teacher'
    ], function () {
          Route::get('', [TeacherController::class, 'showAll']); //route to get all teachers
          Route::get('/credentials', [TeacherCredentialController::class, 'showCredentials']); //route to get all teacher credentials details
          Route::get('/verified', [TeacherController::class, 'showAllVerified']); // route to get all verified teachers
          Route::get('/verified/{id}', [TeacherController::class, 'showOneVerified']); // route to get verified teacher by id
          Route::get('/unverified', [TeacherController::class, 'showAllNotVerified']); // route to get all unverified teachers
          Route::get('/unverified/{id}', [TeacherController::class, 'showOneNotVerified']); // route to get unverified teacher by id
          Route::get('/requests', [ParentRequestController::class, 'ShowUserRequests']); // retrieve all requests made for a teacher
          Route::get('/{id}', [TeacherController::class, 'showOne']); // route to get teacher by id
    }
);
Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => 'parent'
    ], function () {
          Route::get('', [ParentController::class, 'showAll']); // route to get all parents
          Route::post('/request-teacher', [ParentRequestController::class, 'requestTeacher']); // route to request teacher
          Route::post('/cancel-request-teacher', [ParentRequestController::class, 'cancelRequestTeacher']); // route to cancel request for a teacher
          Route::get('/requests', [ParentRequestController::class, 'ShowUserRequests']); // retrieve all requests made by a parent
          Route::get('/{id}', [ParentController::class, 'showOne']); // route to get parent by id
    }
);





// Reset Password Routes

// this route is used to send the password-reset mail
Route::post('/user/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

// this route is for the password reset link sent to the user
Route::get('/user/reset-password/{token}', function($token) {
    return ['token' => $token]; 
})->middleware('guest')->name('password.reset');

//this route handles password reset submission
Route::post('/user/reset-password', function(Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password) 
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.update');
