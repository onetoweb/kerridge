<?php

namespace Onetoweb\Kerridge;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Kerridge Api Client
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb B.V.
 */
class Client
{
    /**
     * @var string
     */
    private $baseUrl;
    
    /**
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * @param array $data
     * 
     * @return string
     */
    public function createXml(array $array): string
    {
        $bodyArray = $this->arrayChangeKeyCaseRecursive($array);
        
        return ArrayToXml::convert(['SOAP-ENV:Body' => $bodyArray], [
            'rootElementName' => 'SOAP-ENV:Envelope',
            '_attributes' => [
                'xmlns:SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:ns1' => "{$this->baseUrl}vhp_exml.pcd",
                'xmlns:xsd' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
                'SOAP-ENV:encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
            ],
        ], true, 'UTF-8');
    }
    
    /**
     * @param array $array
     * @param string $case = CASE_UPPER
     * 
     * @return array
     */
    public function arrayChangeKeyCaseRecursive(array $array, string $case = CASE_UPPER): array
    {
        return array_map(function($item) {
            if(is_array($item)) {
                $item = $this->arrayChangeKeyCaseRecursive($item);
            }
            return $item;
        }, array_change_key_case($array, CASE_UPPER));
    }
    
    /**
     * @param string $data
     * @param string $soapAction
     * 
     * @return string
     */
    public function request(string $body, string $soapAction): string
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/xml',
                'SOAPACTION' => $soapAction
            ],
            RequestOptions::BODY => $body
        ];
        
        $client = new GuzzleClient();
        $response  = $client->request('POST', "{$this->baseUrl}vhp_exml.pcd", $options);
        
        return $response->getBody()->getContents();
    }
    
    /**
     * @param array $order
     * 
     * @return array
     */
    public function createOrder(array $order): array
    {
        $xmlOrder = $this->createXml([
            'weborder' => [
                'orders' => [
                    'order' => $order
                ]
            ]
        ]);
        
        $xmlResponse =  $this->request($xmlOrder, 'WEBORDER');
        
        $xml = simplexml_load_string($xmlResponse);
        
        return [
            'order' => (string) current($xml->xpath('//ORDER')),
            'status' => (string) current($xml->xpath('//STATUS')),
            'melding' => (string) current($xml->xpath('//MELDING')),
        ];
    }
}