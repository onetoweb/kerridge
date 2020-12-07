<?php

namespace Onetoweb\Kerridge;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use Spatie\ArrayToXml\ArrayToXml;
use SimpleXMLElement;

/**
 * Kerridge Api Client
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb B.V.
 */
class Client
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    
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
        return array_map(function($item) use ($case) {
            if(is_array($item)) {
                $item = $this->arrayChangeKeyCaseRecursive($item, $case);
            }
            return $item;
        }, array_change_key_case($array, $case));
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
    
    /**
     * @param SimpleXMLElement $element
     * 
     * @return mixed string|int|float|null
     */
    public function getValue(SimpleXMLElement $element, string $key, string $type = TYPE_STRING)
    {
        $value = $element->{$key};
        
        settype($value, $type);
        
        if ($value !== '') {
            return $value;
        }
        
        return null;
    }
    
    /**
     * @param SimpleXMLElement $element
     * 
     * @return array
     */
    public function getItem(SimpleXMLElement $element): array
    {
        $item = [
            'itemid' => $this->getValue($element, 'Itemid', self::TYPE_INT),
            'name' => $this->getValue($element, 'Name', self::TYPE_STRING),
            'sortvalue' => $this->getValue($element, 'Sortvalue', self::TYPE_STRING),
            'item_type' => $this->getValue($element, 'ItemType', self::TYPE_STRING),
            'is_blocked' => $this->getValue($element, 'IsBlocked', self::TYPE_INT),
            'width' => $this->getValue($element, 'Width', self::TYPE_FLOAT),
            'length' => $this->getValue($element, 'Length', self::TYPE_FLOAT),
            'weight' => $this->getValue($element, 'Weight', self::TYPE_FLOAT),
            'height' => $this->getValue($element, 'Height', self::TYPE_FLOAT),
            'volume' => $this->getValue($element, 'Volume', self::TYPE_FLOAT),
            'brand' => $this->getValue($element, 'Brand', self::TYPE_STRING),
            'type' => $this->getValue($element, 'Type', self::TYPE_STRING),
            'sales_unit' => $this->getValue($element, 'SalesUnit', self::TYPE_INT),
            'price_unit' => $this->getValue($element, 'PriceUnit', self::TYPE_INT),
            'rent_price_per_day' => $this->getValue($element, 'RentPricePerDay', self::TYPE_FLOAT),
            'rent_price_per_day_incl_vat' => $this->getValue($element, 'RentPricePerDayInclVat', self::TYPE_FLOAT),
            'sales_price' => $this->getValue($element, 'SalesPrice', self::TYPE_FLOAT),
            'sales_price_incl_vat' => $this->getValue($element, 'SalesPriceInclVat', self::TYPE_FLOAT),
            'subitems' => [],
            'extra_description' => [],
            'synonims' => [],
            'alternatives' => [],
        ];
        
        if ($element->Subitems->Subitem->count() > 0) {
            
            foreach ($element->Subitems->children() as $child) {
                
                $item['subitems'][] = [
                    'sub_itemid' => $this->getValue($child, 'SubItemid', self::TYPE_INT),
                    'seq_sub_item' => $this->getValue($child, 'SeqSubItem', self::TYPE_INT),
                    'quantiy' => $this->getValue($child, 'Quantiy', self::TYPE_FLOAT),
                    'is_mandatory' => $this->getValue($child, 'IsMandatory', self::TYPE_INT),
                    'is_price_included_mainitem' => $this->getValue($child, 'IsPriceIncludedMainitem', self::TYPE_INT),
                ];
                
            }
        }
        
        if ($element->ExtraDescriptions->ExtraDescription->count() > 0) {
            
            foreach ($element->ExtraDescriptions->children() as $child) {
                
                $item['extra_description'][] = [
                    'seq_extra_descr' => $this->getValue($child, 'SeqExtraDescr', self::TYPE_INT),
                    'extra_description_line' => $this->getValue($child, 'ExtraDescriptionLine', self::TYPE_STRING),
                ];
                
            }
        }
        
        if ($element->Alternatives->Alternative->count() > 0) {
            
            foreach ($element->Alternatives->children() as $child) {
                
                $item['alternatives'][] = [
                    'sequence_alt' => $this->getValue($child, 'SequenceAlt', self::TYPE_INT),
                    'item_id_alt' => $this->getValue($child, 'ItemIdAlt', self::TYPE_INT),
                ];
                
            }
        }
        
        return $item;
    }
    
    /**
     * @param string $content
     * 
     * @return array
     */
    public function getItemsFromString(string $content): array
    {
        $items = [];
        
        $xml = simplexml_load_string($content);
        
        foreach ($xml->children() as $child) {
            $items[] = $this->getItem($child);
        }
        
        return $items;
    }
}