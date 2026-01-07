<?php

namespace App\Services;

class AutoloadStatisticsService
{
    public function getStatistics(): array
    {
        $declaredClasses = get_declared_classes();
        $declaredInterfaces = get_declared_interfaces();
        $declaredTraits = get_declared_traits();
        
        return [
            'classes' => count($declaredClasses),
            'interfaces' => count($declaredInterfaces),
            'traits' => count($declaredTraits),
            'total' => count($declaredClasses) + count($declaredInterfaces) + count($declaredTraits),
        ];
    }

    public function getDetailedStatistics(): array
    {
        $declaredClasses = get_declared_classes();
        
        $laravelClasses = array_filter($declaredClasses, function($class) {
            return str_starts_with($class, 'Illuminate\\');
        });
        
        $appClasses = array_filter($declaredClasses, function($class) {
            return str_starts_with($class, 'App\\');
        });
        
        return [
            'statistics' => $this->getStatistics(),
            'laravel_classes' => [
                'count' => count($laravelClasses),
                'list' => array_values($laravelClasses),
            ],
            'app_classes' => [
                'count' => count($appClasses),
                'list' => array_values($appClasses),
            ],
        ];
    }

    public function getAllClasses(): array
    {
        return [
            'classes' => get_declared_classes(),
            'interfaces' => get_declared_interfaces(),
            'traits' => get_declared_traits(),
        ];
    }
}