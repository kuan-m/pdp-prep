<?php
/**
 * Тест производительности: Фибоначчи (арифметические операции)
 * 
 * Этот тест проверяет производительность вычисления чисел Фибоначчи
 * с использованием различных алгоритмов для оценки влияния OPcache и JIT
 * на арифметические операции.
 */

// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

$startTime = microtime(true);
$startMemory = memory_get_usage();

// Параметры теста
$n = isset($_GET['n']) ? (int)$_GET['n'] : 40;
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 1;
/**
 * Рекурсивное вычисление Фибоначчи (медленное, для демонстрации)
 * time O(N^2), space O(N)
 */
function fibonacciRecursive($n) {
    if ($n <= 1) {
        return $n;
    }
    return fibonacciRecursive($n - 1) + fibonacciRecursive($n - 2);
}

/**
 * Итеративное вычисление Фибоначчи (быстрое)
 * time O(N), space O(1)
 */
function fibonacciIterative($n) {
    if ($n <= 1) {
        return $n;
    }
    
    $a = 0;
    $b = 1;
    
    for ($i = 2; $i <= $n; $i++) {
        $temp = $a + $b;
        $a = $b;
        $b = $temp;
    }
    
    return $b;
}

/**
 * Матричное вычисление Фибоначчи (оптимизированное)
 * time O(logN), space O(logN)
 */
function fibonacciMatrix($n) {
    if ($n <= 1) {
        return $n;
    }
    
    $matrix = [[1, 1], [1, 0]];
    $result = matrixPower($matrix, $n - 1);
    return $result[0][0];
}

function matrixPower($matrix, $n) {
    if ($n == 1) {
        return $matrix;
    }
    
    if ($n % 2 == 0) {
        $half = matrixPower($matrix, $n / 2);
        return matrixMultiply($half, $half);
    } else {
        $half = matrixPower($matrix, ($n - 1) / 2);
        return matrixMultiply($matrix, matrixMultiply($half, $half));
    }
}

function matrixMultiply($a, $b) {
    return [
        [
            $a[0][0] * $b[0][0] + $a[0][1] * $b[1][0],
            $a[0][0] * $b[0][1] + $a[0][1] * $b[1][1]
        ],
        [
            $a[1][0] * $b[0][0] + $a[1][1] * $b[1][0],
            $a[1][0] * $b[0][1] + $a[1][1] * $b[1][1]
        ]
    ];
}

$results = [];

// Тест 1: Итеративный алгоритм (основной тест)
$iterativeStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $result = fibonacciIterative($n);
}
$iterativeTime = (microtime(true) - $iterativeStart) * 1000;

$results['iterative'] = [
    'result' => $result,
    'time_ms' => round($iterativeTime, 4),
    'time_per_iteration_ms' => round($iterativeTime / $iterations, 4),
    'iterations' => $iterations
];

// Тест 2: Матричный алгоритм
$matrixStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $matrixResult = fibonacciMatrix($n);
}
$matrixTime = (microtime(true) - $matrixStart) * 1000;

$results['matrix'] = [
    'result' => $matrixResult,
    'time_ms' => round($matrixTime, 4),
    'time_per_iteration_ms' => round($matrixTime / $iterations, 4),
    'iterations' => $iterations
];

// Тест 3: Рекурсивный алгоритм (только для малых n)
if ($n <= 30) {
    $recursiveStart = microtime(true);
    $recursiveResult = fibonacciRecursive($n);
    $recursiveTime = (microtime(true) - $recursiveStart) * 1000;
    
    $results['recursive'] = [
        'result' => $recursiveResult,
        'time_ms' => round($recursiveTime, 4),
        'iterations' => 1
    ];
}

$endTime = microtime(true);
$endMemory = memory_get_usage();

$response = [
    'test' => 'Fibonacci (Arithmetic)',
    'parameters' => [
        'n' => $n,
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
echo json_encode($response, JSON_PRETTY_PRINT);

