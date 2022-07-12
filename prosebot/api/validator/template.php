<?php
/**
 * Validate template text-condition pair and get error messages
 * METHOD: POST
 * PARAMS: $context, $lang
 * RESPONSE: string Error messages
 */

require_once(__DIR__.'/../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

header("Access-Control-Allow-Origin: ".$_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once(__DIR__ . '/../../validator/templatesvalidator.php');

$data = json_decode(file_get_contents('php://input'));
if (isset($_GET['context']) && isset($_GET['lang'])) {
    if ($data !== null && property_exists($data, "text") && property_exists($data, "condition")) {
        try {
            $validator = new TemplatesValidator($_GET['lang'], $_GET['context']);
            $validator->validate_inputs($data->text, $data->condition);
        } catch (Exception $e) {
            echo str_replace(["<b>", "</b>"], "", $e->getMessage());
        }
        http_response_code(200);
    }
    else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        echo json_encode(array("message" => "Bad request. Data is empty."));
        http_response_code(400);
    }
    else {
        http_response_code(200);
    }
} else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}
