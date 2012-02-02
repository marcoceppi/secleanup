<?php

// Demonstrates how Stack.PHP's data retrieval methods can
// be exposed to other clients, such as JavaScript code.

require_once 'config.php';

// The very first thing we do is output CORS headers
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Origin: *');

// Retrieve all Stack Exchange sites across all pages.
$response = API::Sites();

// Build an array with the response
$sites = array();

while($site = $response->Fetch(TRUE))
    $sites[] = $site->Data();

// Encode the data as JSON
$json = json_encode(array('items' => $sites, 'has_more' => FALSE));

// Output the data according to the parameters specified
if(isset($_GET['callback']))
{
    header('Content-type: application/javascript');
    echo "{$_GET['callback']}($json);";
}
else
{
    header('Content-type: application/json');
    echo $json;
}

?>