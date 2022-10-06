<?php

declare(strict_types=1);

namespace Leaf\Eien;

/**
 * Leaf Eien Server
 * -----------
 * High-speed, high-performance server for your Leaf apps
 * 
 * @package Leaf\Eien
 * @author Michael Darko <mychi.darko@gmail.com>
 * @version 0.1.0
 */
class Server {
  protected $host;
  protected $http;

  public function __construct(string $host = '127.0.0.1', int $port = 9501)
  {
    $this->host = $host;
    $this->http = new \Swoole\HTTP\Server($host, $port);
  }

  /**
   * Wrap your Leaf application with Eien
   * 
   * @param \Leaf\App $The leaf application to serve
   */
  public function wrap($app)
  {
    $this->http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use($app) {
      $adapter = new Adapter($app);
      $adapter->intercept($request, $response);
      $adapter->process();
    });

    return $this;
  }

  public function listen(callable $callback = null)
  {
    $this->http->on("start", function (\Swoole\Http\Server $server) use ($callback) {
      if ($callback) $callback($server);
    });

    $this->http->start();
  }
}
