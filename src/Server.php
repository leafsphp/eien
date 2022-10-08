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
   * Configure the server
   */
  public function config(array $config)
  {
    $this->http->set($config);
    return $this;
  }

  /**
   * Wrap your Leaf application with Eien
   * 
   * @param callable $runApp Run the leaf app and return it's data
   */
  public function wrap($runApp)
  {
    $this->http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use($runApp) {
      $adapter = new Adapter();
      $adapter->intercept($request, $response);

      ob_start();
      call_user_func($runApp);
      $data = ob_get_clean();

      $adapter->process([
        'body' => $data,
        'headers' => \Leaf\Config::get('response.data')['headers'] ?? [],
      ]);
    });

    return $this;
  }

  public function listen(callable $callback = null)
  {
    $this->http->on("start", function (\Swoole\Http\Server $server) use ($callback) {
      if ($callback) {
        $callback($server);
      } else {
        echo "Leaf Eien server started on http://127.0.0.1:{$server->port}";
      }
    });

    $this->http->start();
  }
}
