<?php

declare(strict_types=1);

namespace Leaf\Eien\Adapter;

/**
 * Leaf Eien Adapter for WebSockets
 */
class WebSocket
{
    public static \Swoole\WebSocket\Frame $frame;
    public static \Swoole\WebSocket\Server $server;
    public static \Swoole\Http\Request $request;
    public static array $globalServer;
    public static array $httpRequest;
    public static array $httpHeaders;
    public static array $httpCookies;

    public static function intercept(array $config)
    {
        if (isset($config['request'])) {
            static::$request = $config['request'];
        }

        if (isset($config['server'])) {
            static::$server = $config['server'];
        }

        if (isset($config['frame'])) {
            static::$frame = $config['frame'];
        }
    }

    public static function forceStateReset(): void
    {
        \Leaf\Config::set('request.headers', []);
        \Leaf\Config::set('response.headers', []);
        \Leaf\Config::set('response.cookies', []);
        \Leaf\Config::set('response.redirect', null);
    }

    public static function process($events): void
    {
        $origin = static::$server['host'];
        // $sourcenUrl = static::$frame->srcElement->url;

        static::$server->push(static::$frame->fd, static::$frame);
    }
}
