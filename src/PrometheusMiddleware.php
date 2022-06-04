<?php

namespace OneContent\Prometheus;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

class PrometheusMiddleware
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CollectorRegistry
     */
    protected $registry;

    /**
     * @var Histogram
     */
    protected $requestDurationHistogram;

    public function __construct(CollectorRegistry $collectorRegistry)
    {
        $this->registry = $collectorRegistry;;
        $this->initRouteMetrics();
    }

    public function handle(Request $request, Closure $next)
    {
        $start         = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->request = $request;

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        $route_name = $this->getRouteName();
        $method     = $request->getMethod();
        $status     = $response->getStatusCode();

        $duration              = microtime(true) - $start;
        $duration_milliseconds = $duration * 1000.0;
        $this->countRequest($route_name, $method, $status, $duration_milliseconds);

        return $response;
    }

    public function initRouteMetrics()
    {
        $namespace = config('prometheus.namespace');
        $buckets   = config('prometheus.histogram_buckets');
        $labelNames = $this->getRequestCounterLabelNames();

        $name                           = 'name';
        $help                           = 'help';

        $this->requestDurationHistogram = $this->registry->getOrRegisterHistogram(
            $namespace, $name, $help, $labelNames, $buckets
        );
    }

    protected function getRequestCounterLabelNames()
    {
        return [
            'method',
            'code',
            'route',
        ];
    }

    public function countRequest($route, $method, $statusCode, $duration_milliseconds)
    {
        $labelValues = [(string) $route, (string) $method, (string) $statusCode];
        $this->requestDurationHistogram->observe($duration_milliseconds, $labelValues);
    }

    /**
     * Get route name
     *
     * @return string
     */
    protected function getRouteName()
    {
        return \Request::path() ?: 'unnamed';
    }
}
