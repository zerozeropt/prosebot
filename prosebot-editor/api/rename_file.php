<?php
/**
 * Change name of a template file
 * METHOD: PUT
 * PARAMS: $context, $lang, $name
 * DATA: $filename string The new name for the file
 * RESPONSE: string Confirmation or error
 */

require_once(__DIR__.'/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: ".$_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));
if (isset($_GET["name"]) && isset($_GET["context"]) && isset($_GET["lang"])) {
    $dir = __DIR__.'/../templates/'.$_GET["context"].'/'.$_GET["lang"].'/';
    if (file_exists($dir.$_GET["name"]) && $data !== null && property_exists($data, "filename")) {
        rename($dir.$_GET["name"], $dir.$data->filename);
        echo json_encode(array("message" => "File name updated."));
        http_response_code(200);
    }
    else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        echo json_encode(array("message" => "File does not exist."));
        http_response_code(400);
    }
    else http_response_code(200);
}
else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}