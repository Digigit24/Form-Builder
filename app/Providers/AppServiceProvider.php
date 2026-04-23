<?php

namespace App\Providers;

use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Neon requires the endpoint ID to be passed explicitly because XAMPP's
        // bundled libpq is too old to support SNI-based endpoint routing.
        $this->app->bind('db.connector.pgsql', function () {
            return new class extends PostgresConnector {
                protected function getDsn(array $config): string
                {
                    $dsn = parent::getDsn($config);

                    $host = $config['host'] ?? '';
                    if (str_contains($host, 'neon.tech')) {
                        $endpointId = explode('.', $host)[0];
                        if (! str_ends_with($dsn, ';')) {
                            $dsn .= ';';
                        }
                        $dsn .= "options=endpoint={$endpointId}";
                    }

                    return $dsn;
                }
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
