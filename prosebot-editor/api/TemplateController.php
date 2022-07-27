<?php
require_once(__DIR__ . '/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: " . $_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

/**
 * Class for controlling templates endpoints
 * 
 * @author zerozero.pt
 */
class TemplateController
{
    /**
     * Delete template file
     * Method: DELETE
     * URL: "/templates/<template_id>"
     * PARAMS: context, language
     * @param string $id Name of the template file
     */
    public function delete($id)
    {
        if (isset($_GET["context"]) && isset($_GET["lang"])) {
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            $path = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/' . $id . ".json";
            if (file_exists($path)) {
                unlink($path);
                echo json_encode(array("message" => "File deleted."));
                http_response_code(200);
            } else {
                echo json_encode(array("message" => "Unable to locate file."));
                http_response_code(400);
            }
        } else {
            echo json_encode(array("message" => "Unable to locate file."));
            http_response_code(400);
        }
    }

    /**
     * Get list of template files names
     * Method: GET
     * URL: "/templates/names"
     * PARAMS: context, language
     */
    public function names()
    {
        if (isset($_GET['context']) && isset($_GET['lang'])) {
            $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/';
            $templates = scandir($dir);
            $data = [];
            foreach ($templates as $template_name) {
                if ($template_name == "." || $template_name == "..")
                    continue;
                array_push($data, mb_substr($template_name, 0, strlen($template_name) - 5));
            }
            echo json_encode($data);
            http_response_code(200);
        } else {
            echo json_encode(array("message" => "Bad request."));
            http_response_code(400);
        }
    }

    /**
     * Get data of template file
     * Method: GET
     * URL: "/templates/<template_id>/data"
     * PARAMS: context, language
     * @param string $id Name of the template file
     */
    public function data($id)
    {
        if (isset($_GET['context']) && isset($_GET['lang'])) {
            $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/';
            echo file_get_contents($dir . $id . ".json");
            http_response_code(200);
        } else {
            echo json_encode(array("message" => "Bad request."));
            http_response_code(400);
        }
    }

    /**
     * Create a template file
     * Method: POST
     * URL: "/templates"
     * PARAMS: context, language
     * DATA: 
     *  - data: Data to write to the file
     *  - filename: Name of the new file
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'));
        if (isset($_GET["context"]) && isset($_GET["lang"])) {
            if ($data !== null && property_exists($data, "data") && property_exists($data, "filename")) {
                $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/' . $data->filename;
                if (file_exists($dir)) {
                    echo json_encode(array("message" => "File already exists."));
                    http_response_code(400);
                } else {
                    $file = fopen($dir, "w");
                    fwrite($file, json_encode($data->data));
                    fclose($file);
                    echo json_encode(array("message" => "File created."));
                    http_response_code(200);
                }
            } else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
                echo json_encode(array("message" => "Bad request."));
                http_response_code(400);
            } else {
                http_response_code(200);
            }
        } else {
            echo json_encode(array("message" => "Bad request."));
            http_response_code(400);
        }
    }

    /**
     * Edit the data of a template file
     * Method: PUT
     * URL: "/templates/<template_id>/data"
     * PARAMS: context, language
     * DATA: 
     *  - data: Data to write to the file
     * @param string $id Name of the template file
     */
    public function update_data($id)
    {
        $data = json_decode(file_get_contents('php://input'));
        if (isset($_GET["context"]) && isset($_GET["lang"])) {
            $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/' . $id . ".json";
            if (file_exists($dir) && $data !== null && property_exists($data, "data")) {
                $file = fopen($dir, "w");
                fwrite($file, json_encode($data->data));
                fclose($file);
                echo file_get_contents($dir);
                http_response_code(200);
            } else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
                echo json_encode(array("message" => "File does not exist."));
                http_response_code(400);
            } else {
                http_response_code(200);
            }
        } else {
            echo json_encode(array("message" => "Bad request."));
            http_response_code(400);
        }
    }

    /**
     * Rename a template file
     * Method: PUT
     * URL: "/templates/<template_id>/name"
     * PARAMS: context, language
     * DATA: 
     *  - filename: Name of the new file
     * @param string $id Name of the template file
     */
    public function update_name($id)
    {
        $data = json_decode(file_get_contents('php://input'));
        if (isset($_GET["context"]) && isset($_GET["lang"])) {
            $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/';
            if (file_exists($dir . $id . ".json") && $data !== null && property_exists($data, "filename")) {
                rename($dir . $id . ".json", $dir . $data->filename . ".json");
                echo json_encode(array("message" => "File name updated."));
                http_response_code(200);
            } else if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
                echo json_encode(array("message" => "File does not exist."));
                http_response_code(400);
            } else {
                http_response_code(200);
            }
        } else {
            echo json_encode(array("message" => "Bad request."));
            http_response_code(400);
        }
    }
}
