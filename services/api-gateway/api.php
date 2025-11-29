<?php
/**
 * API Gateway для тестов производительности
 * Проксирует запросы к другим сервисам
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'run_all':
        runAllTests();
        break;
    case 'get_services':
        getServices();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function runAllTests() {
    $test = $_GET['test'] ?? '';
    $params = $_GET['params'] ?? '{}';
    
    if (empty($test)) {
        http_response_code(400);
        echo json_encode(['error' => 'Test is required']);
        return;
    }
    
    $services = ['opcache-only', 'opcache-jit', 'no-opcache'];
    $results = [];
    $startTime = microtime(true);
    
    // Запускаем тесты параллельно
    $multiHandle = curl_multi_init();
    $handles = [];
    
    // Определяем базовый URL в зависимости от окружения
    $isK8s = getenv('KUBERNETES_SERVICE_HOST') !== false;
    
    // В Kubernetes обращаемся к nginx сервисам, локально - к localhost
    $servicesConfig = [
        'opcache-only' => $isK8s ? 'http://nginx-opcache-only:80' : 'http://localhost:8080',
        'opcache-jit' => $isK8s ? 'http://nginx-opcache-jit:80' : 'http://localhost:8081',
        'no-opcache' => $isK8s ? 'http://nginx-no-opcache:80' : 'http://localhost:8082'
    ];
    
    $paramsArray = json_decode($params, true) ?: [];
    $queryString = http_build_query($paramsArray);
    
    foreach ($services as $service) {
        $url = $servicesConfig[$service] . '/shared/3_jit_opcache/' . basename($test) . '?' . $queryString;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        curl_multi_add_handle($multiHandle, $ch);
        $handles[$service] = $ch;
    }
    
    // Выполняем запросы
    $running = null;
    do {
        curl_multi_exec($multiHandle, $running);
        curl_multi_select($multiHandle);
    } while ($running > 0);
    
    // Собираем результаты
    foreach ($handles as $service => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_multi_remove_handle($multiHandle, $ch);
        curl_close($ch);
        
        if ($error || $httpCode !== 200) {
            $results[$service] = [
                'success' => false,
                'error' => $error ?: "HTTP $httpCode"
            ];
        } else {
            // Убираем возможные пробелы/переносы строк перед JSON
            $response = trim($response);
            
            // Проверяем, что ответ начинается с { или [
            if (empty($response) || (substr($response, 0, 1) !== '{' && substr($response, 0, 1) !== '[')) {
                $results[$service] = [
                    'success' => false,
                    'error' => 'Invalid response format. Response starts with: ' . substr($response, 0, 100)
                ];
            } else {
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
                    $results[$service] = [
                        'success' => false,
                        'error' => 'Invalid JSON response: ' . json_last_error_msg() . '. Response preview: ' . substr($response, 0, 200)
                    ];
                } else {
                    $results[$service] = [
                        'success' => true,
                        'data' => $data
                    ];
                }
            }
        }
    }
    
    curl_multi_close($multiHandle);
    
    $totalTime = (microtime(true) - $startTime) * 1000;
    
    echo json_encode([
        'success' => true,
        'test' => $test,
        'total_time_ms' => round($totalTime, 2),
        'results' => $results
    ]);
}

function getServices() {
    echo json_encode([
        'services' => [
            [
                'id' => 'opcache-only',
                'name' => 'OPcache Only',
                'description' => 'OPcache включен, JIT отключен',
                'port' => 8080
            ],
            [
                'id' => 'opcache-jit',
                'name' => 'OPcache + JIT',
                'description' => 'OPcache и JIT включены',
                'port' => 8081
            ],
            [
                'id' => 'no-opcache',
                'name' => 'No OPcache',
                'description' => 'OPcache и JIT отключены',
                'port' => 8082
            ]
        ]
    ]);
}
