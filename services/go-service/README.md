# Go Test Service

HTTP сервер на Go, который выполняет тот же функционал что и PHP-FPM тестовые скрипты (`test.php` и `test-light.php`).

## Endpoints

- `GET /test` - тяжелые вычисления (100,000 итераций)
  - Эквивалент PHP `test.php`
  - Выполняет: `sqrt(i) * sin(i) + cos(i)` для i от 0 до 100,000

- `GET /test-light` - легкие вычисления (10,000 итераций)
  - Эквивалент PHP `test-light.php`
  - Выполняет: `sqrt(i) * sin(i) + cos(i)` для i от 0 до 10,000

- `GET /health` - проверка здоровья сервиса

## Формат ответа

Все endpoints возвращают JSON в следующем формате:

```json
{
  "pid": 12345,
  "memory_usage": 1048576,
  "memory_peak": 2097152,
  "execution_time_ms": 125.50,
  "timestamp": "2024-01-15 10:30:45",
  "load": "light"  // только для /test-light
}
```

Формат идентичен PHP версии для возможности сравнения производительности.


## Тестирование

### Нагрузочное тестирование

Используйте Makefile из корня проекта:

```bash
# Тяжелый тест
make test-go

# Легкий тест
make test-go-light
```

## Сравнение с PHP

Этот сервис реализует ту же логику что и PHP скрипты для сравнения производительности:

- **PHP**: `shared/2_php_fpm/test.php` и `test-light.php`
- **Go**: `services/go-service` с endpoints `/test` и `/test-light`

