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

    $_COOKIE = [];
    if (is_array($request->cookie)) {
      foreach ($request->cookie as $key => $value) {
        $_COOKIE[$key] = $value;
      }
    }

    \Leaf\Config::set('request.headers', $request->header);

    $this->request = $request;
    $this->response = $response;

    return $this;
  }

  public function forceStateReset()
  {
    \Leaf\Config::set('request.headers', []);
    \Leaf\Config::set('response.headers', []);
    \Leaf\Config::set('response.cookies', []);
    \Leaf\Config::set('response.context', []);
  }

  public function process($appData)
  {
    $headers = $appData['headers'] ?? [];
    $cookies = $appData['cookies'] ?? [];
    $body = $appData['body'] ?? '';

    foreach ($headers as $hkey => $hvalue) {
      $this->response->header($hkey, $hvalue);
    }

    foreach ($cookies as $ckey => $cvalue) {
      $this->response->setcookie($ckey, $cvalue[0], $cvalue[1]);
    }

    $this->forceStateReset();
    
    if ($appData['redirect']) {
      $this->response->redirect(...$appData['redirect']);
    } else {
      $this->response->end($body);
    }
  }
}
