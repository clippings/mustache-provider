<?php

namespace Mustache;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Mustache_Engine;
use Mustache_Loader_ArrayLoader;
use Mustache_Loader_StringLoader;
use Mustache_Loader_FilesystemLoader;

/**
 * Mustache service provider for Pimple.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['mustache.options.defaults'] = function (Container $app) {
            return array(
                'loader'          => $app['mustache.loader'],
                'partials_loader' => $app['mustache.partials_loader'],
                'helpers'         => $app['mustache.helpers'],
                'charset'         => $app['charset'],
            );
        };

        $app['mustache.options'] = function (Container $app) {
            return $app['mustache.options.defaults'];
        };

        $app['mustache'] = $app->factory(function (Container $app) {
            if (isset($app['logger'])) {
                $defaults['logger'] = $app['logger'];
            }

            $options = array_replace(
                $app['mustache.options.defaults'],
                $app['mustache.options']
            );

            return new Mustache_Engine($options);
        });

        $app['mustache.loader'] = $app->factory(function (Container $app) {
            if (!isset($app['mustache.path'])) {
                return new Mustache_Loader_StringLoader;
            }

            return new Mustache_Loader_FilesystemLoader($app['mustache.path']);
        });

        $app['mustache.partials_loader'] = $app->factory(function (Container $app) {
            if (isset($app['mustache.partials_path'])) {
                return new Mustache_Loader_FilesystemLoader($app['mustache.partials_path']);
            }

            if (isset($app['mustache.partials'])) {
                return new Mustache_Loader_ArrayLoader($app['mustache.partials']);
            }

            return $app['mustache.loader'];
        });

        $app['mustache.helpers'] = array();
    }
}
