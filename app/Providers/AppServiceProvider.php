<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // The UI is Bootstrap 5; the default paginator emits Tailwind markup.
        // Laravel 8 only bundles Bootstrap 4 views, so use the local BS5 view.
        \Illuminate\Pagination\Paginator::defaultView('pagination.bootstrap-5');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination.simple-bootstrap-5');

        // @selected and @checked ship with Laravel 9; on 8 they pass through as
        // literal text and silently leave every option unselected. Backported
        // here so the views can read the same as they would on a current release.
        \Illuminate\Support\Facades\Blade::directive('selected', function ($expression) {
            return "<?php if ($expression): echo 'selected'; endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('checked', function ($expression) {
            return "<?php if ($expression): echo 'checked'; endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('vite', function ($expression) {
            return "<?php
                \$assets = $expression;
                if (!is_array(\$assets)) {
                    \$assets = [\$assets];
                }
                \$isDev = file_exists(public_path('hot'));
                \$isStyle = function (\$path) {
                    return (bool) preg_match('/\.(css|scss|sass|less|styl)\$/i', \$path);
                };
                if (\$isDev) {
                    \$url = rtrim(file_get_contents(public_path('hot')));
                    echo '<script type=\"module\" src=\"'.\$url.'/@vite/client\"></script>';
                    foreach (\$assets as \$asset) {
                        if (\$isStyle(\$asset)) {
                            echo '<link rel=\"stylesheet\" href=\"'.\$url.'/'.\$asset.'\">';
                        } else {
                            echo '<script type=\"module\" src=\"'.\$url.'/'.\$asset.'\"></script>';
                        }
                    }
                } else {
                    \$manifestPath = public_path('build/manifest.json');
                    if (file_exists(\$manifestPath)) {
                        \$manifest = json_decode(file_get_contents(\$manifestPath), true);
                        foreach (\$assets as \$asset) {
                            if (isset(\$manifest[\$asset])) {
                                if (\$isStyle(\$manifest[\$asset]['file'])) {
                                    echo '<link rel=\"stylesheet\" href=\"/build/'.\$manifest[\$asset]['file'].'\">';
                                } else {
                                    echo '<script type=\"module\" src=\"/build/'.\$manifest[\$asset]['file'].'\"></script>';
                                    if (isset(\$manifest[\$asset]['css'])) {
                                        foreach (\$manifest[\$asset]['css'] as \$css) {
                                            echo '<link rel=\"stylesheet\" href=\"/build/'.\$css.'\">';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ?>";
        });
    }
}
