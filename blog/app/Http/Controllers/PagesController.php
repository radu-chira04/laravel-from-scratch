<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $tasks = [
            'go to the store',
            'go to the market',
            'go to work',
            'go to concert'
        ];

        //return view('welcome')->withTasks($tasks)->withUser('user');
        return view('welcome', ['tasks' => $tasks, 'user' => 'tstUser']);
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
}
