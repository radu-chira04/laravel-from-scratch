<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class AmazonApiController extends Controller
{
    public function justCall()
    {
        $client = new \GuzzleHttp\Client();
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));

        $sellerId = '';
        $mwsAuthToken = '';
        $awsSecretKeyId = '';
        $awsSecretAccessKey = '';
        $signatureMethod = 'HmacSHA256';
        $marketplaceId = '';

        $version = '2013-09-01';
        $timestamp = $dateTime->format(DATE_ISO8601);

        $query = [
            'AWSAccessKeyId' => $awsSecretKeyId,
            'Action' => 'ListOrders',
            'MWSAuthToken' => $mwsAuthToken,
            'MarketplaceId.Id.1' => $marketplaceId,
            'SellerId' => $sellerId,
            'SignatureVersion' => '2',
            'SignatureMethod' => $signatureMethod,
            'LastUpdatedAfter' => '2019-08-01T18%3A12%3A21',
            'Timestamp' => $timestamp,
            'Version' => $version,
        ];

        ksort($query);

        // Create our new request
        foreach ($query as $key => $value) {
            // We need to be sure we properly encode the value of our parameter
            $key = str_replace("%7E", "~", rawurlencode($key));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $request_array[] = $key . '=' . $value;
        }

        // Put our & symbol at the beginning of each of our request variables and put it in a string
        $new_request = implode('&', $request_array);

        // Create our signature string
        $signature_string = "POST\nmws.amazonservices.com\n/Orders/2013-09-01\n{$new_request}";

        // Create our signature using hash_hmac
        $signature = urlencode(base64_encode(hash_hmac('sha256', $signature_string, $awsSecretAccessKey, true)));
        $query['Signature'] = $signature;

        $return = [];

        $requestOptions = [
            'headers' => [
                'Content-Type' => 'text/xml',
                'x-amazon-user-agent' => 'AmazonJavascriptScratchpad/1.0 (Language=Javascript)'
            ],
            'query' => $query
        ];

        try {
            $response = $client->post('https://mws-eu.amazonservices.com/Orders/' . $version, $requestOptions);
            $return['response'] = $response;
        } catch (Exception $e) {
            $return['error'] = $e->getMessage();
        }

        return json_encode($response);
    }
}
