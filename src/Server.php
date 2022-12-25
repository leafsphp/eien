<?php

declare(strict_types=1);

namespace Leaf\Eien;

use Leaf\Config;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

use Leaf\Eien\Adapter\Http as HttpAdapter;
use Leaf\Eien\Adapter\WebSocket as WebSocketAdapter;

/**
 * Leaf Eien Server
 * -----------
 * High-speed, high-performance server for your Leaf apps
 * 
 * @package Leaf\Eien
 * @author Michael Darko <mychi.darko@gmail.com>
 * @version 0.1.0
 */
class Server
{
    protected WebSocketServer $server;

    public function __construct(string $host = '127.0.0.1', int $port = 9501)
    {
        $this->server = new WebSocketServer(
            $host,
            $port
        );
    }

    /**
     * Configure the server
     */
    public function config(array $config): Server
    {
        $this->server->set($config);
        return $this;
    }

    public function on(string $event, callable $callback): Server
    {
        if ($event === 'request' || $event === 'start') {
            return $this;
        }

        $this->server->on($event, $callback);
        return $this;
    }

    /**
     * Wrap your Leaf application with Eien
     * 
     * @param mixed $runApp Run the leaf app and return it's data
     */
    public function wrap($runApp): Server
    {
        $this->server->on('request', function ($request, $response) use ($runApp) {
            $adapter = new HttpAdapter();
            $adapter->intercept($request, $response);

            ob_start();

            if ($runApp instanceof \Leaf\App) {
                $runApp->run();
            } else {
                $runApp();
            }

            $adapter->process(ob_get_clean());
        });

        $this->server->on('open', function ($server, Request $request) {
            WebSocketAdapter::$httpHeaders = $request->header ?? [];
            WebSocketAdapter::$httpCookies = $request->cookie ?? [];
            WebSocketAdapter::$globalServer = $request->server ?? [];
        });

        $this->server->on('message', function (WebSocketServer $server,  Frame $frame) {
            $events = Config::get('eien.events');
            WebSocketAdapter::intercept(['server' => $server, 'frame' => $frame]);

            ob_start();

            if (isset($events[WebSocketAdapter::$globalServer['request_uri']])) {
                $events[WebSocketAdapter::$globalServer['request_uri']]($server, $frame);
            } else {
                isset($events['*']) ?
                    $events['*']($server, $frame) :
                    call_user_func(function () {
                        echo json_encode(['error' => 'No event handler found']);
                    });
            }

            $server->push($frame->fd, ob_get_clean());
        });

        return $this;
    }

    public function listen(?callable $callback = null)
    {
        $this->server->on('start', function (\Swoole\Http\Server $server) use ($callback) {
            if ($callback) {
                $callback($server);
            } else {
                echo "Leaf Eien server started on http://{$server->host}:{$server->port}\n";
            }
        });

        $this->server->on('close', function ($server, int $fd) {
            echo "connection close: {$fd}\n";
        });

        $this->server->on('disconnect', function ($server, int $fd) {
            echo "connection disconnect: {$fd}\n";
        });

        if (!$this->server->start()) {
            echo "Leaf Eien server failed to start\n";
        };
    }
}
