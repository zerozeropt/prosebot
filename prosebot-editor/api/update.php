<?php
/**
 * Update template file data
 * METHOD: PUT
 * PARAMS: $context, $lang, $name
 * DATA: $file JSON The new data for the file
 * RESPONSE: JSON|string New data of the file or error message
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));
if (isset($_GET["context"]) && isset($_GET["lang"]) && isset($_GET["name"])) {
    $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/' . $_GET["name"];
    if (file_exists($dir) && property_exists($data, "file")) {
        $file = fopen($dir, "w");
        fwrite($file, json_encode($data->file));
        fclose($file);
        echo file_get_contents($dir);
        http_response_code(200);
    } else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        echo json_encode(array("message" => "File does not exist."));
        http_response_code(400);
    }
} else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}
