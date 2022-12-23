<?php

namespace OneContent\Prometheus;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot() : void
    {
        DB::listen(function ($query) {
            if (request()->is('metrics') === false && request()->is('_debugbar/*') === false) {
                $querySql = '[omitted]';
                $type = strtoupper(strtok((string)$query->sql, ' '));
                if (config('prometheus.collect_full_sql_query')) {
                    $querySql = $this->cleanupSqlString((string)$query->sql);
                }
                $labels = array_values(array_filter([
                    request()->path(),
                    $querySql,
                    $type
                ]));
                $this->app->get('prometheus.sql.histogram')->observe($query->time, $labels);
            }
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register() : void
    {
        $this->app->singleton('prometheus.sql.histogram', function ($app) {
            return $app['prometheus']->getOrRegisterHistogram(
                'sql_query_duration',
                'SQL query duration histogram',
                array_values(array_filter([
                    'route',
                    'query',
                    'query_type'
                ])),
                config('prometheus.sql_buckets') ?? null
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() : array
    {
        return [
            'prometheus.sql.histogram',
        ];
    }

    /**
     * Cleans the SQL string for registering the metric.
     * Removes repetitive question marks and simplifies "VALUES" clauses.
     *
     * @return string
     */
    private function cleanupSqlString(string $sql): string
    {
        $sql = preg_replace('/(VALUES\s*)(\([^\)]*+\)[,\s]*+)++/i', '$1()', $sql);
        $sql = preg_replace('/(\s*\?\s*,?\s*){2,}/i', '?', $sql);
        $sql = str_replace('"', '', $sql);

        return empty($sql) ? '[error]' : $sql;
    }
}
