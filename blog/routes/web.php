<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PageController@home');
Route::get('/contact', 'PageController@contact');
Route::get('/about', 'PageController@about');

//Route::get('/', function () {
//    $tasks = [
//        'go to the store',
//        'go to the market',
//        'go to work',
//        'go to concert'
//    ];
//    //return view('welcome')->withTasks($tasks)->withUser('user');
//    return view('welcome', ['tasks' => $tasks, 'user' => 'tstUser']);
//});


//Route::get('/contact', function () {
//    return view('contact');
//});


//Route::get('/about', function () {
//    return view('about');
//});

