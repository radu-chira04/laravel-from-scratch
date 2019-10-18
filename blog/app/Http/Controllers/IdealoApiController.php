<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IdealoApiController extends Controller
{
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const ENDOINT = 'shop/:shopId/offer/';
    const URL = 'https://import.idealo.com/';

    private $curlHandles = [];
    private $curlMultiHandle = null;

    const QUEUE_LIMIT = 5;
    private $urlQueue = [];

    private $testItemForIdealo = [
        "sku" => "ABC13112",
        "title" => "test title",
        "price" => "18.80",
        "url" => "http://www.idealo.de/",
        "paymentCosts" => [
            "PAYPAL" => "1.25",
            "CREDIT_CARD" => "3.00",
            "CASH_IN_ADVANCE" => "0.00",
            "PAYPAL" => "1.25"
        ],
        "deliveryCosts" => [
            "DHL" => "1.00"
        ],
    ];

    private $testSKUs = [
        'ABC13111', 'ABC13222', 'ABC13112', 'ABC13112', 'ABC13212',
        'ABC13122', 'ABC13123', 'ABC13114', 'ABC13115', 'ABC13116',
        'ABC13117', 'ABC13118', 'ABC13119', 'ABC13120',
    ];

    public function getLoginDetails()
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

    public function justCall()
    {
        $shopId = $this->getLoginDetails()->shop_id;
        $token = $this->getLoginDetails()->access_token;

        $payload = $this->testItemForIdealo;
        $url = self::URL . str_replace(':shopId', $shopId, self::ENDOINT) . $payload['sku'];

        $this->printArray($payload, __LINE__);

        $header = array();
        $header[] = 'Authorization: Bearer ' . $token;
        $method = 'GET';# values like GET, PUT, DELETE

        $ch = curl_init($url);
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

        $return = [];
        $return['method'] = $method;
        $response = (array)json_decode($response);
        if(isset($response['fieldErrors']) || isset($response['generalErrors']) || isset($response['error'])) {
            $return['line'] = 'error on line: ' . __LINE__;
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $return['response'] = $response;
        $return['status_code'] = $statusCode;

        return json_encode($return);
    }

    public function multiCurlCall()
    {
        $this->testAddToQueue();
        die("<br/>end at line " . __LINE__ . "<br/>");

        $urls = [];
        $urls['ABC13111'] = "https://import.idealo.com/shop/309564/offer/ABC13111";
        $urls['ABC13222'] = "https://import.idealo.com/shop/309564/offer/ABC13222";
        $urls['ABC13112'] = "https://import.idealo.com/shop/309564/offer/ABC13112";

        $this->printArray($urls, __LINE__);

        $result = $this->multiCurlRequests($urls);

        $this->printArray($result, __LINE__);

        $this->closeCurlConnection();
        die("<br/>end at line " . __LINE__ . "<br/>");
    }

    private function testAddToQueue()
    {
        $items = $this->testSKUs;
        $shopId = $this->getLoginDetails()->shop_id;
        echo "count items: " . count($items) . "<br/>";
        for ($i = 0; $i <= count($items); $i++) {
            if (isset($items[$i])) {
                $url = self::URL . str_replace(':shopId', $shopId, self::ENDOINT) . $items[$i];
                $this->urlQueue[$i] = $url;
                if (count($this->urlQueue) == self::QUEUE_LIMIT) {
                    // process urls
                    $this->printArray($this->urlQueue, __LINE__);
                    $this->urlQueue = [];
                }
            }
        }

        if (count($this->urlQueue)) {
            // proccess remained urls
            $this->printArray($this->urlQueue, __LINE__);
        }
    }

    public function multiCurlRequests($urls)
    {
        $i = 0;
        $header = array();
        $header[] = 'Authorization: Bearer ' . $this->getLoginDetails()->access_token;
        $method = 'GET';# GET, PUT, DELETE
        if (empty($this->curlMultiHandle)) {
            $this->curlMultiHandle = curl_multi_init();
        }

        foreach ($urls as $sku => $url) {
            if (empty($this->curlHandles[$i])) {
                $this->curlHandles[$i] = curl_init($url);
            }
            curl_setopt($this->curlHandles[$i], CURLOPT_URL, $url);
            switch ($method) {

                case 'GET':
                    $header[] = 'Accept: application/json';
                    curl_setopt($this->curlHandles[$i], CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($this->curlHandles[$i], CURLOPT_POSTFIELDS, $sku);
                    break;

                case 'DELETE':
                    $header[] = 'Accept: application/json';
                    curl_setopt($this->curlHandles[$i], CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($this->curlHandles[$i], CURLOPT_POSTFIELDS, $sku);
                    break;
            }

            curl_setopt($this->curlHandles[$i], CURLOPT_HTTPHEADER, $header);
            curl_setopt($this->curlHandles[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($this->curlMultiHandle, $this->curlHandles[$i]);
            $i++;
        }

        $running = null;
        do {
            curl_multi_exec($this->curlMultiHandle, $running);
        } while ($running > 0);

        $result = [];
        foreach ($this->curlHandles as $k => $ch) {
            $result[$k] = curl_multi_getcontent($ch);
            if (json_decode($result[$k])) {
                $result[$k] = (array)json_decode($result[$k]);
            }
            curl_multi_remove_handle($this->curlMultiHandle, $ch);
        }
        return $result;
    }

    private function closeCurlConnection()
    {
        if (is_array($this->curlHandles) && !empty($this->curlHandles)) {
            try {
                foreach ($this->curlHandles as $ch) {
                    curl_close($ch);
                }
                curl_multi_close($this->curlMultiHandle);
            } catch (\Throwable $throwable) {
                echo $throwable->getMessage() . "<br/>";
                die("<br/>end at line " . __LINE__ . "<br/>");
            }
        }
    }

    private function printArray($array, $line)
    {
        echo "<br/>";
        echo "line: " . $line;
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        echo "<br/>";
    }
}
