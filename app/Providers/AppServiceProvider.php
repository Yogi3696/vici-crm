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
