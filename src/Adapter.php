<?php

declare(strict_types=1);

namespace Leaf\Eien;

/**
 * Eien Adapter for Swoole to Leaf Http
 */
class Adapter
{
  /**@var \Leaf\App */
  protected $application;

  protected $request;
  protected $response;

  public function __construct($application)
  {
    $this->application = $application;
  }

  public function intercept(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
  {
    foreach ($request->server as $key => $value) {
      $_SERVER[strtoupper($key)] = $value;
    }

    $this->request = $request;
    $this->response = $response;

    return $this;
  }

  public function process()
  {
    \ob_start();
    $this->application->run();
    $output = ob_get_clean();

    $this->response->end($output);
  }
}
