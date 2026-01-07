<?php

namespace App\Http\Controllers;

use App\Services\AutoloadStatisticsService;
use Illuminate\Http\JsonResponse;

class AutoloadStatisticsController extends Controller
{
    public function __construct(
        private AutoloadStatisticsService $service
    ) {}

    public function index(): JsonResponse
    {
        $stats = $this->service->getStatistics();
        
        return response()->json([
            'success' => true,
            'data' => [
                'counts' => [
                    'classes' => $stats['classes'],
                    'interfaces' => $stats['interfaces'],
                    'traits' => $stats['traits'],
                    'total' => $stats['total'],
                ]
            ],
        ]);
    }

    public function detailed(): JsonResponse
    {
        $detailed = $this->service->getDetailedStatistics();
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'classes' => $detailed['statistics']['classes'],
                    'interfaces' => $detailed['statistics']['interfaces'],
                    'traits' => $detailed['statistics']['traits'],
                    'total' => $detailed['statistics']['total'],
                ],
                'laravel_classes' => [
                    'count' => $detailed['laravel_classes']['count'],
                    'list' => $detailed['laravel_classes']['list'],
                ],
                'app_classes' => [
                    'count' => $detailed['app_classes']['count'],
                    'list' => $detailed['app_classes']['list'],
                ]
            ],
        ]);
    }
}