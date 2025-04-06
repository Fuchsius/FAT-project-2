<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Database file configuration
$dataFile = 'properties.json';

// Helper function to read data from the JSON file
function getData() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, '[]'); // Create file if it doesn't exist
    }
    $json = file_get_contents($dataFile);
    if ($json === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to read data file']);
        exit;
    }
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
        exit;
    }
    return $data;
}

// Helper function to save data to the JSON file
function saveData($data) {
    global $dataFile;
    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to encode JSON: ' . json_last_error_msg()]);
        exit;
    }
    if (file_put_contents($dataFile, $json) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save data']);
        exit;
    }
}

// Helper function to serve static files
function serveStaticFile($path) {
    if (file_exists($path)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'html':
                header('Content-Type: text/html');
                break;
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }
        readfile($path);
        exit;
    }
    return false;
}

// Handle incoming requests
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    // Serve static files first
    if ($requestUri === '/') {
        serveStaticFile('index.html');
    }
    
    // Handle API routes
    if (preg_match('/^\/properties\/(\d+)$/', $requestUri, $matches)) {
        $id = (int)$matches[1];
        $data = getData();

        if ($method === 'DELETE') {
            // Delete a property by ID
            $found = false;
            foreach ($data as $index => $property) {
                if ($property['id'] === $id) {
                    unset($data[$index]);
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $data = array_values($data); // Re-index the array
                saveData($data);
                http_response_code(204);
                echo json_encode(['message' => 'Property deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Property not found']);
            }
        } elseif ($method === 'PUT') {
            // Update a property by ID
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input: ' . json_last_error_msg()]);
                exit;
            }

            $found = false;
            foreach ($data as $index => $property) {
                if ($property['id'] === $id) {
                    $data[$index] = array_merge($property, $input);
                    $found = true;
                    break;
                }
            }

            if ($found) {
                saveData($data);
                http_response_code(200);
                echo json_encode($data[$index]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Property not found']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
    } else {
        switch ($requestUri) {
            case '/properties':
                if ($method === 'GET') {
                    // Get all properties
                    echo json_encode(getData());
                } elseif ($method === 'POST') {
                    // Add a new property
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid JSON input: ' . json_last_error_msg()]);
                        exit;
                    }
                    
                    $data = getData();
                    // Generate unique ID using timestamp
                    $input['id'] = time();
                    $data[] = $input;
                    saveData($data);
                    http_response_code(201);
                    echo json_encode($input);
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method Not Allowed']);
                }
                exit;

            default:
                // Try to serve static files for other routes
                $filePath = ltrim($requestUri, '/');
                if (!serveStaticFile($filePath)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Route Not Found']);
                }
                exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}