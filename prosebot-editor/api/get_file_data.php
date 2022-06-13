<?php
/**
 * Get the data of a specific template file for a given context, language and name
 * METHOD: GET
 * PARAMS: $context, $lang, $name
 * RESPONSE: JSON Data of the file
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (isset($_GET['context']) && isset($_GET['lang']) && isset($_GET['name'])) {
    $dir = __DIR__.'/../templates/'.$_GET["context"].'/'.$_GET["lang"].'/';
    echo file_get_contents($dir.$_GET['name']);
    http_response_code(200);
}
else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}
