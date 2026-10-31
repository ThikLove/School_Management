<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ClassSubjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

/*
| Guest-only pages: logged-in users are sent to the dashboard instead.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
});

/*
| Pages for any logged-in user. Each controller action also checks the
| model policy, so a teacher or student only ever sees their own data.
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Shared by all roles: teachers and students get read-only, filtered views.
    Route::resource('classes', SchoolClassController::class)->parameters(['classes' => 'schoolClass']);
    Route::resource('students', StudentController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('attendance', AttendanceController::class)->except('show');

    /*
    | Admin-only management pages.
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('enrollments', EnrollmentController::class)->except('show');

        Route::post('/classes/{schoolClass}/subjects', [ClassSubjectController::class, 'store'])->name('classes.subjects.store');
        Route::delete('/classes/{schoolClass}/subjects/{assignment}', [ClassSubjectController::class, 'destroy'])->name('classes.subjects.destroy');
    });
});
