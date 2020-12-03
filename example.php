<?php

require 'vendor/autoload.php';

use Onetoweb\Kerridge\Client;

$baseUrl  = 'base_url';
$client = new Client($baseUrl);

// create order
$result = $client->createOrder([
    'soort' => 0,
    'opbouw' => 1,
    'afbouw' => 1,
    'externklntnr' => '',
    'externkenmrk' => '002',
    'bron' => 'WSH',
    'huurbegdatum' => '2017-06-14',
    'huurenddatum' => '2017-06-16',
    'betaalmethode' => '',
    'uwref' => '',
    'contractnaam' => 'ENCR',
    'adres' => [
        'adrnaam' => 'AGP',
        'adrkontakt' => 'Anne Vogels',
        'titel' => '',
        'aanspreektitel' => 'Mevr.',
        'voorvoegsel' => 'de het en een',
        'voorletters' => 'A.H.M.',
        'familienaam' => 'Zwennes',
        'voornaam' => 'Anne',
        'particulier' => 'N',
        'factemail' => 'anne.vogels@agp.nl',
        'btwnummer' => 'NLANNE',
        'kvk' => '1234567',
        'internet' => 'www.agp.nl',
        'banknummer' => 'NLB010000000',
        'adradres' => 'Gijsbrecht van Amstelstraat',
        'adrhuisnummer' => '310',
        'adrpostkode' => '1046AE',
        'adrplaats' => 'Veghel',
        'adrtelefoon' => '0413387777',
        'adrtelefoon2' => '',
        'adremail' => '',
        'adrland' => 'Nederland',
        'adrlandcode' => 'NL',
    ],
    'bezorgen' => [
        'ophaal' => 'N',
        'ophlokatie' => '',
        'ophkontakt' => 'mevrouw Natasja van Wal',
        'ophadres' => 'Jimuiden 47A',
        'ophhuisnummer' => '',
        'ophhuisnummertoevoeging' => '',
        'ophplaats' => 'Amsterdam',
        'ophpostkode' => '1111AB',
        'ophland' => 'Nederland',
        'ophlandcode' => 'NL',
        'ophbegtijd' => '00.00',
        'ophendtijd' => '00.00',
        'ophkosten' => '',
        'ophopmerking' => '',
        'ophtelefoon' => '0201234567',
        'ophtelefoon2' => '',
        'ophemail' => 'natasja@catering.nl',
    ],
    'artikelen' => [
        'artikel' => [[
            'artikelnr' => '1001001',
            'besteld' => '10',
        ] , [
            'artikelnr' => '1001003',
            'besteld' => '12',
        ] , [
            'artikelnr' => '1001004',
            'besteld' => '9',
        ]],
    ],
    'opmerkingen' => [
        'opmerking' => 'Notities over de bestelling',
    ]
]);