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
    \Leaf\Config::set('response.redirect', null);
  }

  public function process($body)
  {
    foreach (\Leaf\Config::get('response.headers') ?? [] as $hkey => $hvalue) {
      $this->response->header($hkey, $hvalue);
    }

    foreach (\Leaf\Config::get('response.cookies') ?? [] as $ckey => $cvalue) {
      $this->response->setcookie($ckey, $cvalue[0], $cvalue[1]);
    }

    if (\Leaf\Config::get('response.redirect')) {
      $this->response->redirect(...\Leaf\Config::get('response.redirect'));
    } else {
      $this->response->end($body ?? '');
    }

    $this->forceStateReset();
  }
}
