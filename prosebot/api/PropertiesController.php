<?php
require_once(__DIR__ . '/../global_vars.php');
require_once(__DIR__ . '/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: " . $_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

/**
 * Class for controlling properties endpoints
 * 
 * @author zerozero.pt
 */
class PropertiesController
{
    /**
     * Get list of contexts
     * Method: GET
     * URL: "/properties/contexts"
     */
    public function contexts()
    {
        echo json_encode(array_keys(get_globals()['contexts']));
        http_response_code(200);
    }

    /**
     * Get list of languages
     * Method: GET
     * URL: "/properties/languages"
     */
    public function languages()
    {
        echo json_encode(array_keys(get_globals()['languages']));
        http_response_code(200);
    }
}
