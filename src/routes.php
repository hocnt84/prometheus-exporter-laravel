<?php

use Illuminate\Support\Facades\Route;
use OneContent\Prometheus\MetricsController;

$route = Route::get(
    config('prometheus.metrics_route_path'), MetricsController::class . '@getMetrics'
);

if ($name = config('prometheus.metrics_route_name')) {
    $route->name($name);
}

$middleware = config('prometheus.metrics_route_middleware');

if ($middleware) {
    $route->middleware($middleware);
}
