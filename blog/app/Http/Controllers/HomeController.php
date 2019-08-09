<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class HomeController extends Controller
{
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const ENDOINT = 'shop/:shopId/offer/';
    const URL = 'https://import.idealo.com/';

    private $testItemForIdealo = [
        "sku" => "ABC13111",// ABC13111, ABC13222
        "title" => "test title",
        "price" => "13.80",
        "url" => "http://www.idealo.de/",
        "paymentCosts" => [
            "PAYPAL" => "1.23",
            "CREDIT_CARD" => "2.99",
            "CASH_IN_ADVANCE" => "0.00",
            "PAYPAL" => "1.23"
        ],
        "deliveryCosts" => [
            "DHL" => "0.99"
        ],
    ];


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

    public function getShopDetails()
    {
        $url = 'https://api.idealo.com/mer/businessaccount/api/v1/oauth/token';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, self::USERNAME . ':' . self::PASSWORD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response);
    }

    public function testIdealoApi()
    {
        $shopId = $this->getShopDetails()->shop_id;
        $token = $this->getShopDetails()->access_token;

        $payload = $this->testItemForIdealo;
        $url = self::URL . str_replace(':shopId', $shopId, self::ENDOINT) . $payload['sku'];

        // https://import.idealo.com/shop/309564/offer/abc3434
        // return json_encode(['url' => $url, 'shop_id' => $shopId]);

        $ch = curl_init($url);

        $header = array();
        $header[] = 'Authorization: Bearer ' . $token;
        $method = 'GET';# values like GET, PUT, DELETE

        switch ($method) {

            case 'PUT':
                $contentJson = json_encode($payload);
                $header[] = 'Content-Type: application/json; charset=UTF-8';
                $header[] = 'Content-Length: ' . strlen($contentJson);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $contentJson);
                break;

            case 'GET':
                $header[] = 'Accept: application/json';
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload['sku']);
                break;

            case 'DELETE':
                $header[] = 'Accept: application/json';
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload['sku']);
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $line = 0;
        $response = (array)json_decode($response);
        if(isset($response['fieldErrors']) || isset($response['generalErrors']) || isset($response['error'])) {
            $line = __LINE__;
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_encode(['response' => $response, 'status_code' => $statusCode, 'line' => $line]);
    }



}
