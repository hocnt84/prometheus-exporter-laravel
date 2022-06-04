# prometheus-exporter-laravel

## Installation

```bash
composer require one-content/prometheus
```

## Configuration

The package has a default configuration which uses the following environment variables.
```
PROMETHEUS_NAMESPACE=app

PROMETHEUS_METRICS_ROUTE_ENABLED=true
PROMETHEUS_METRICS_ROUTE_PATH=metrics
PROMETHEUS_METRICS_ROUTE_MIDDLEWARE=null

PROMETHEUS_STORAGE_ADAPTER=memory

REDIS_HOST=localhost
REDIS_PORT=6379
PROMETHEUS_REDIS_PREFIX=PROMETHEUS_
```

To customize the configuration file, publish the package configuration using Artisan.
```bash
php artisan vendor:publish --provider="OneContent\Prometheus\PrometheusServiceProvider"
```

You can then edit the generated config at `app/config/prometheus.php`.

### Storage Adapters

The storage adapter is used to persist metrics across requests.  The `memory` adapter is enabled by default, meaning
data will only be persisted across the current request.  We recommend using the `redis` or `apc` adapter in production
environments.
The `PROMETHEUS_STORAGE_ADAPTER` env var is used to specify the storage adapter.

If `redis` is used, the `REDIS_HOST` and `REDIS_PORT` vars also need to be configured.

### Exporting Metrics

The package adds a `/metrics` end-point, enabled by default, which exposes all metrics gathered by collectors.

This can be turned on/off using the `PROMETHEUS_METRICS_ROUTE_ENABLED` var, and can also be changed using the
`PROMETHEUS_METRICS_ROUTE_PATH` var.

If you would like to protect this end-point, you can write any custom middleware and enable it using
`PROMETHEUS_METRICS_ROUTE_MIDDLEWARE`.
