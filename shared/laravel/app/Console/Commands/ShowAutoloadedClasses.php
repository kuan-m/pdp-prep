<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowAutoloadedClasses extends Command
{
    protected $signature = 'autoload:count {--details : Show detailed class list}';
    
    protected $description = 'Show count of autoloaded classes at Laravel startup';

    public function handle()
    {
        // Получаем все загруженные классы
        $declaredClasses = get_declared_classes();
        $declaredInterfaces = get_declared_interfaces();
        $declaredTraits = get_declared_traits();
        
        $total = count($declaredClasses) + count($declaredInterfaces) + count($declaredTraits);
        
        $this->info("📊 Статистика загруженных классов:");
        $this->table(
            ['Тип', 'Количество'],
            [
                ['Classes', count($declaredClasses)],
                ['Interfaces', count($declaredInterfaces)],
                ['Traits', count($declaredTraits)],
                ['TOTAL', $total],
            ]
        );

        if ($this->option('details')) {
            $this->newLine();
            $this->info("📦 Laravel классы:");
            $laravelClasses = array_filter($declaredClasses, function($class) {
                return str_starts_with($class, 'Illuminate\\');
            });
            $this->line("Количество: " . count($laravelClasses));
            
            $this->newLine();
            $this->info("🔧 App классы:");
            $appClasses = array_filter($declaredClasses, function($class) {
                return str_starts_with($class, 'App\\');
            });
            $this->line("Количество: " . count($appClasses));
            
            if ($this->confirm('Показать полный список классов?', false)) {
                foreach ($declaredClasses as $class) {
                    $this->line($class);
                }
            }
        }

        return Command::SUCCESS;
    }
}