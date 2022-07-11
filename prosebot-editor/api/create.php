<?php
/**
 * Create template file
 * METHOD: POST
 * PARAMS: $context, $lang
 * DATA: $file JSON   Data of the file
 *       $name string Name of the file
 * RESPONSE: string Confirmation or error
 */

require_once(__DIR__.'/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: ".$_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));
if (isset($_GET["context"]) && isset($_GET["lang"])) {
    if ($data !== null && property_exists($data, "file") && property_exists($data, "name")) {
        $dir = __DIR__.'/../templates/'.$_GET["context"].'/'.$_GET["lang"].'/'.$data->name;
        if (file_exists($dir)) {
            echo json_encode(array("message" => "File already exists."));
            http_response_code(400);
        }
        else {
            $file = fopen($dir, "w");
            fwrite($file, json_encode($data->file));
            fclose($file);
            echo json_encode(array("message" => "File created."));
            http_response_code(200);
        }
    }
    else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        echo json_encode(array("message" => "Bad request."));
        http_response_code(400);
    }
    else  http_response_code(200);
}
else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}