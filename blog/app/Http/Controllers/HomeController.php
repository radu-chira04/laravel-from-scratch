<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function testWithGuzzle()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request(
            'GET',
            'https://packagist.org/search.json',
            ['query' => ['q' => 'plentymarkets']]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

}
