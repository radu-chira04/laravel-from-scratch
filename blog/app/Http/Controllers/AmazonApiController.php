<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AmazonApiController extends Controller
{
    public function justCall()
    {
        $response = [];
        $response['api_call'] = true;
        return json_encode($response);
    }
}
