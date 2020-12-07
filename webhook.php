<?php

require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Onetoweb\Kerridge\Client;

$baseUrl  = 'base_url';
$client = new Client($baseUrl);

// example handling webhooks using the symfony http kernel
// https://symfony.com/doc/current/components/http_kernel.html
// requires symfony http kernel: (composer require symfony/http-kernel)
$request = Request::createFromGlobals();
$content = $request->getContent();

// alternatively example using basic php
$content = file_get_contents('php://input');


// get items from string
$items = $client->getItemsFromString($content);