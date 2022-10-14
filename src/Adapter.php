<?php

declare(strict_types=1);

namespace Leaf\Eien;

/**
 * Eien Adapter for Swoole to Leaf Http
 */
class Adapter
{
  protected $request;
  protected $response;

  public function intercept(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
  {
    foreach ($request->server as $key => $value) {
      $_SERVER[strtoupper($key)] = $value;
    }

    $this->request = $request;
    $this->response = $response;

    return $this;
  }

  public function process($appData)
  {
    $headers = $appData['headers'] ?? [];
    $cookies = $appData['cookies'] ?? [];
    $body = $appData['body'] ?? '';

    foreach ($headers as $key => $value) {
      $this->response->header($key, $value);
    }

    foreach ($cookies as $cookie) {
      $this->response->setcookie(...$cookie);
    }

    $this->response->end($body);
  }
}
