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

/*
    GET     /projects           (index)
    GET     /projects/create    (create)
    GET     /projects/$id       (show)
    GET     /projects/$id/edit  (edit)
    POST    /projects           (store)
    PATH    /projects/$id       (update)
    DELETE  /projects/$id       (destroy/delete)
 */


# pages controller
Route::get('/', 'PagesController@home');
Route::get('/contact', 'PagesController@contact');
Route::get('/about', 'PagesController@about');

# projects controller
Route::get('/projects', 'ProjectsController@index');
Route::get('/projects/create', 'ProjectsController@create');
Route::post('/projects', 'ProjectsController@store');
Route::get('/projects/{project}/edit', 'ProjectsController@edit');
Route::patch('/projects/{project}', 'ProjectsController@update');
Route::delete('/projects/{project}', 'ProjectsController@destroy');
Route::get('/projects/{project}', 'ProjectsController@show');

# project task controller
Route::patch('/tasks/{task}', 'ProjectTasksController@update');



