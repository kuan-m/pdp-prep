<?php
/**
 * Тест производительности: Матричные операции
 * 
 * Этот тест проверяет производительность различных матричных операций:
 * - Умножение матриц
 * - Транспонирование
 * - Вычисление детерминанта
 * - Сложение матриц
 * 
 * Эти операции нагружают CPU множеством вложенных циклов и вычислений.
 */

// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

$startTime = microtime(true);
$startMemory = memory_get_usage();

// Параметры теста
$matrixSize = isset($_GET['size']) ? (int)$_GET['size'] : 100; // Размер матрицы (NxN)
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 10;

// Ограничиваем параметры
if ($matrixSize < 10 || $matrixSize > 500) {
    $matrixSize = min(max($matrixSize, 10), 500);
}
if ($iterations < 1 || $iterations > 100) {
    $iterations = min(max($iterations, 1), 100);
}

/**
 * Генерация случайной матрицы
 */
function generateMatrix($size) {
    $matrix = [];
    for ($i = 0; $i < $size; $i++) {
        $row = [];
        for ($j = 0; $j < $size; $j++) {
            $row[] = rand(1, 100);
        }
        $matrix[] = $row;
    }
    return $matrix;
}

/**
 * Умножение матриц
 */
function matrixMultiply($a, $b) {
    $n = count($a);
    $result = [];
    
    for ($i = 0; $i < $n; $i++) {
        $row = [];
        for ($j = 0; $j < $n; $j++) {
            $sum = 0;
            for ($k = 0; $k < $n; $k++) {
                $sum += $a[$i][$k] * $b[$k][$j];
            }
            $row[] = $sum;
        }
        $result[] = $row;
    }
    
    return $result;
}

/**
 * Транспонирование матрицы
 */
function matrixTranspose($matrix) {
    $n = count($matrix);
    $result = [];
    
    for ($i = 0; $i < $n; $i++) {
        $row = [];
        for ($j = 0; $j < $n; $j++) {
            $row[] = $matrix[$j][$i];
        }
        $result[] = $row;
    }
    
    return $result;
}

/**
 * Сложение матриц
 */
function matrixAdd($a, $b) {
    $n = count($a);
    $result = [];
    
    for ($i = 0; $i < $n; $i++) {
        $row = [];
        for ($j = 0; $j < $n; $j++) {
            $row[] = $a[$i][$j] + $b[$i][$j];
        }
        $result[] = $row;
    }
    
    return $result;
}

/**
 * Вычисление детерминанта (рекурсивный алгоритм)
 */
function matrixDeterminant($matrix) {
    $n = count($matrix);
    
    if ($n == 1) {
        return $matrix[0][0];
    }
    
    if ($n == 2) {
        return $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
    }
    
    $det = 0;
    for ($j = 0; $j < $n; $j++) {
        $minor = [];
        for ($i = 1; $i < $n; $i++) {
            $row = [];
            for ($k = 0; $k < $n; $k++) {
                if ($k != $j) {
                    $row[] = $matrix[$i][$k];
                }
            }
            if (count($row) > 0) {
                $minor[] = $row;
            }
        }
        $sign = ($j % 2 == 0) ? 1 : -1;
        $det += $sign * $matrix[0][$j] * matrixDeterminant($minor);
    }
    
    return $det;
}

$results = [];

// Генерируем тестовые матрицы
$matrixA = generateMatrix($matrixSize);
$matrixB = generateMatrix($matrixSize);

// Тест 1: Умножение матриц (самая тяжелая операция - O(n³))
$multiplyStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $result = matrixMultiply($matrixA, $matrixB);
}
$multiplyTime = (microtime(true) - $multiplyStart) * 1000;

$results['matrix_multiply'] = [
    'matrix_size' => $matrixSize,
    'iterations' => $iterations,
    'time_ms' => round($multiplyTime, 4),
    'time_per_iteration_ms' => round($multiplyTime / $iterations, 4),
    'operations_per_second' => round($iterations / ($multiplyTime / 1000), 2),
    'complexity' => 'O(n³)'
];

// Тест 2: Транспонирование (O(n²))
$transposeStart = microtime(true);
for ($i = 0; $i < $iterations * 10; $i++) {
    $transposed = matrixTranspose($matrixA);
}
$transposeTime = (microtime(true) - $transposeStart) * 1000;

$results['matrix_transpose'] = [
    'matrix_size' => $matrixSize,
    'iterations' => $iterations * 10,
    'time_ms' => round($transposeTime, 4),
    'time_per_iteration_ms' => round($transposeTime / ($iterations * 10), 4),
    'operations_per_second' => round(($iterations * 10) / ($transposeTime / 1000), 2),
    'complexity' => 'O(n²)'
];

// Тест 3: Сложение матриц (O(n²))
$addStart = microtime(true);
for ($i = 0; $i < $iterations * 10; $i++) {
    $sum = matrixAdd($matrixA, $matrixB);
}
$addTime = (microtime(true) - $addStart) * 1000;

$results['matrix_add'] = [
    'matrix_size' => $matrixSize,
    'iterations' => $iterations * 10,
    'time_ms' => round($addTime, 4),
    'time_per_iteration_ms' => round($addTime / ($iterations * 10), 4),
    'operations_per_second' => round(($iterations * 10) / ($addTime / 1000), 2),
    'complexity' => 'O(n²)'
];

// Тест 4: Вычисление детерминанта (только для небольших матриц)
if ($matrixSize <= 50) {
    $detStart = microtime(true);
    for ($i = 0; $i < min($iterations, 5); $i++) {
        $det = matrixDeterminant($matrixA);
    }
    $detTime = (microtime(true) - $detStart) * 1000;
    
    $results['matrix_determinant'] = [
        'matrix_size' => $matrixSize,
        'iterations' => min($iterations, 5),
        'time_ms' => round($detTime, 4),
        'time_per_iteration_ms' => round($detTime / min($iterations, 5), 4),
        'result' => $det,
        'complexity' => 'O(n!)'
    ];
}

$endTime = microtime(true);
$endMemory = memory_get_usage();

$response = [
    'test' => 'Matrix Operations',
    'parameters' => [
        'matrix_size' => $matrixSize,
        'iterations' => $iterations
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

// Выводим JSON
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

