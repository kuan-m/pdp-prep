<?php
/**
 * Тест производительности: JSON операции
 * 
 * Этот тест проверяет производительность операций с JSON:
 * - Кодирование (encode)
 * - Декодирование (decode)
 * - Работа с большими JSON структурами
 * - Множественные операции
 */

// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Увеличиваем лимиты для больших JSON операций
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 600);

header('Content-Type: application/json');

$startTime = microtime(true);
$startMemory = memory_get_usage();

// Параметры теста
$dataSize = isset($_GET['size']) ? (int)$_GET['size'] : 1000; // Количество элементов
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 100;
$depth = isset($_GET['depth']) ? (int)$_GET['depth'] : 3; // Глубина вложенности

// Ограничиваем параметры для предотвращения переполнения памяти
if ($dataSize < 1 || $dataSize > 50000) {
    $dataSize = min($dataSize, 50000);
}
if ($iterations < 1 || $iterations > 1000) {
    $iterations = min($iterations, 1000);
}
if ($depth < 1 || $depth > 5) {
    $depth = min($depth, 5);
}

/**
 * Генерация тестовых данных
 */
function generateTestData($size, $depth = 3) {
    $data = [];
    for ($i = 0; $i < $size; $i++) {
        $item = [
            'id' => $i,
            'name' => 'Item ' . $i,
            'value' => rand(1, 1000),
            'active' => $i % 2 == 0,
            'tags' => ['tag' . ($i % 10), 'tag' . (($i + 1) % 10)],
            'metadata' => [
                'created' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)),
                'updated' => date('Y-m-d H:i:s'),
                'version' => rand(1, 100)
            ]
        ];
        
        if ($depth > 1) {
            $item['nested'] = generateTestData(min(10, $size / 10), $depth - 1);
        }
        
        $data[] = $item;
    }
    return $data;
}

$results = [];

// Генерируем тестовые данные с обработкой ошибок
try {
    $testData = generateTestData($dataSize, $depth);
    $dataSizeBytes = strlen(serialize($testData));
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate test data',
        'message' => $e->getMessage(),
        'memory_limit' => ini_get('memory_limit'),
        'memory_used' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Тест 1: JSON Encode
$encodeStart = microtime(true);
$encodedStrings = [];
for ($i = 0; $i < $iterations; $i++) {
    $encoded = json_encode($testData, JSON_UNESCAPED_UNICODE);
    $encodedStrings[] = $encoded;
}
$encodeTime = (microtime(true) - $encodeStart) * 1000;
$avgEncodedSize = strlen($encodedStrings[0]);

$results['encode'] = [
    'iterations' => $iterations,
    'data_size_elements' => $dataSize,
    'encoded_size_bytes' => $avgEncodedSize,
    'encoded_size_kb' => round($avgEncodedSize / 1024, 2),
    'time_ms' => round($encodeTime, 4),
    'time_per_iteration_ms' => round($encodeTime / $iterations, 4),
    'throughput_mb_per_sec' => round(($avgEncodedSize * $iterations / 1024 / 1024) / ($encodeTime / 1000), 2)
];

// Тест 2: JSON Decode
$decodeStart = microtime(true);
$decodedData = [];
for ($i = 0; $i < $iterations; $i++) {
    $decoded = json_decode($encodedStrings[$i], true);
    $decodedData[] = $decoded;
}
$decodeTime = (microtime(true) - $decodeStart) * 1000;

$results['decode'] = [
    'iterations' => $iterations,
    'encoded_size_bytes' => $avgEncodedSize,
    'time_ms' => round($decodeTime, 4),
    'time_per_iteration_ms' => round($decodeTime / $iterations, 4),
    'throughput_mb_per_sec' => round(($avgEncodedSize * $iterations / 1024 / 1024) / ($decodeTime / 1000), 2)
];

// Тест 3: Encode + Decode (полный цикл)
$cycleStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $encoded = json_encode($testData, JSON_UNESCAPED_UNICODE);
    $decoded = json_decode($encoded, true);
}
$cycleTime = (microtime(true) - $cycleStart) * 1000;

$results['encode_decode_cycle'] = [
    'iterations' => $iterations,
    'time_ms' => round($cycleTime, 4),
    'time_per_cycle_ms' => round($cycleTime / $iterations, 4)
];

// Тест 4: Работа с большим JSON
$largeDataSize = min($dataSize * 5, 10000); // Ограничиваем размер для предотвращения переполнения
try {
    $largeTestData = generateTestData($largeDataSize, $depth);
} catch (Throwable $e) {
    // Если не удалось сгенерировать большие данные, пропускаем этот тест
    $largeTestData = null;
}

if ($largeTestData !== null) {
    try {
        $largeEncodeStart = microtime(true);
        $largeEncoded = json_encode($largeTestData, JSON_UNESCAPED_UNICODE);
        $largeEncodeTime = (microtime(true) - $largeEncodeStart) * 1000;

        $largeDecodeStart = microtime(true);
        $largeDecoded = json_decode($largeEncoded, true);
        $largeDecodeTime = (microtime(true) - $largeDecodeStart) * 1000;

        $results['large_json'] = [
            'data_size_elements' => $largeDataSize,
            'encoded_size_bytes' => strlen($largeEncoded),
            'encoded_size_mb' => round(strlen($largeEncoded) / 1024 / 1024, 2),
            'encode_time_ms' => round($largeEncodeTime, 4),
            'decode_time_ms' => round($largeDecodeTime, 4),
            'encode_throughput_mb_per_sec' => round((strlen($largeEncoded) / 1024 / 1024) / ($largeEncodeTime / 1000), 2),
            'decode_throughput_mb_per_sec' => round((strlen($largeEncoded) / 1024 / 1024) / ($largeDecodeTime / 1000), 2)
        ];
    } catch (Throwable $e) {
        $results['large_json'] = [
            'error' => 'Failed to process large JSON',
            'message' => $e->getMessage()
        ];
    }
} else {
    $results['large_json'] = [
        'error' => 'Skipped due to memory constraints',
        'data_size_elements' => $largeDataSize
    ];
}

// Тест 5: Множественные операции с разными данными
$mixedStart = microtime(true);
$mixedResults = [];
for ($i = 0; $i < $iterations / 10; $i++) {
    $smallData = generateTestData(100, 2);
    $encoded = json_encode($smallData);
    $decoded = json_decode($encoded, true);
    
    // Модификация
    $decoded[0]['modified'] = true;
    $reEncoded = json_encode($decoded);
    $reDecoded = json_decode($reEncoded, true);
    
    $mixedResults[] = $reDecoded;
}
$mixedTime = (microtime(true) - $mixedStart) * 1000;

$results['mixed_operations'] = [
    'operations_count' => ($iterations / 10) * 4, // encode + decode + encode + decode
    'time_ms' => round($mixedTime, 4),
    'time_per_operation_ms' => round($mixedTime / (($iterations / 10) * 4), 4)
];

$endTime = microtime(true);
$endMemory = memory_get_usage();

$response = [
    'test' => 'JSON Operations',
    'parameters' => [
        'data_size_elements' => $dataSize,
        'iterations' => $iterations,
        'depth' => $depth
    ],
    'results' => $results,
    'performance' => [
        'total_time_ms' => round(($endTime - $startTime) * 1000, 4),
        'memory_used_bytes' => $endMemory - $startMemory,
        'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 4)
    ],
    'php_config' => [
        'opcache_enabled' => (bool)ini_get('opcache.enable'),
        'opcache_enable_cli' => (bool)ini_get('opcache.enable_cli'),
        'opcache_memory_consumption' => ini_get('opcache.memory_consumption'),
        'opcache_max_accelerated_files' => ini_get('opcache.max_accelerated_files'),
        'opcache_validate_timestamps' => (bool)ini_get('opcache.validate_timestamps'),
        'jit_enabled' => ini_get('opcache.jit') && ini_get('opcache.jit') !== 'disable',
        'jit_buffer_size' => ini_get('opcache.jit_buffer_size'),
        'jit_mode' => ini_get('opcache.jit'),
        'php_version' => PHP_VERSION,
        'opcache_status' => function_exists('opcache_get_status') ? @json_decode(json_encode(@opcache_get_status(false)), true) : null,
        'config_summary' => [
            'opcache' => (bool)ini_get('opcache.enable') ? '✅ Включен' : '❌ Отключен',
            'jit' => (ini_get('opcache.jit') && ini_get('opcache.jit') !== 'disable') ? '✅ Включен (' . ini_get('opcache.jit') . ')' : '❌ Отключен',
            'jit_buffer' => ini_get('opcache.jit_buffer_size'),
            'opcache_memory' => ini_get('opcache.memory_consumption') . ' MB',
            'php_version' => PHP_VERSION
        ]
    ]
];

// Выводим JSON с обработкой ошибок
try {
    $json = json_encode($response, JSON_PRETTY_PRINT);
    if ($json === false) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to encode JSON',
            'json_error' => json_last_error_msg(),
            'memory_used' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ], JSON_PRETTY_PRINT);
    } else {
        echo $json;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to output JSON',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

