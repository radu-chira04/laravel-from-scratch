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
        $signature = '';
        $mwsAuthToken = '';
        $awsSecretKeyId = '';
        $awsSecretAccessKey = '';
        $signatureMethod = 'HmacSHA256';

        $version = '2013-09-01';
        $timestamp = $dateTime->format(DATE_ISO8601);

        $query = [
            'AWSAccessKeyId' => $awsSecretKeyId,
            'Action' => 'ListOrders',
            'MWSAuthToken' => $mwsAuthToken,
            'MarketplaceId.Id.1' => 'A1PA6795UKMFR9',
//            'MarketplaceId.Id.2' => '',
//            'MarketplaceId.Id.3' => '',
            'FulfillmentChannel.Channel.1' => 'MFN',
            'PaymentMethod.Method.1' => 'COD',
            'PaymentMethod.Method.2' => 'Other',
            'OrderStatus.Status.1' => 'Unshipped',
            'OrderStatus.Status.2' => 'PendingAvailability',
            'SellerId' => $sellerId,
            'Signature' => $awsSecretKeyId,
            'SignatureVersion' => '2',
            'SignatureMethod' => $signatureMethod,
            'LastUpdatedAfter' => '2019-08-01T18%3A12%3A21',
            'Timestamp' => $timestamp,
            'Version' => $version,
        ];

        $return = [];
        try {
            $response = $client->request(
                'GET', 'https://mws.amazonservices.de/Orders/' . $version,
                ['query' => $query]
            );
            $return['response'] = $response;
        } catch (Exception $e) {
            $return['error'] = $e->getMessage();
        }

        return json_encode($response);
    }
}
