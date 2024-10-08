<?php

declare(strict_types=1);

namespace Leaf\Eien\Adapter;

/**
 * Leaf Eien Adapter for Http
 */
class Http
{
    protected $request;
    protected $response;

    public function intercept($request, $response): self
    {
        foreach ($request->server as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
        }

        $_COOKIE = [];
        foreach ($request->cookie ?? [] as $key => $value) {
            $_COOKIE[$key] = $value;
        }

        $_GET = [];
        foreach ($request->get ?? [] as $key => $value) {
            $_GET[$key] = $value;
        }

        \Leaf\Config::set('request.headers', $request->header);

        $this->request = $request;
        $this->response = $response;

        return $this;
    }

    public function forceStateReset(): void
    {
        \Leaf\Config::set('request.headers', []);
        \Leaf\Config::set('response.headers', []);
        \Leaf\Config::set('response.cookies', []);
        \Leaf\Config::set('response.redirect', null);
    }

    public function process($body): void
    {
        foreach (\Leaf\Config::getStatic('response.headers') ?? [] as $hkey => $hvalue) {
            $this->response->header($hkey, $hvalue);
        }

        foreach (\Leaf\Config::getStatic('response.cookies') ?? [] as $ckey => $cvalue) {
            $this->response->setcookie($ckey, $cvalue[0], $cvalue[1]);
        }

        if (\Leaf\Config::getStatic('response.redirect')) {
            $this->response->redirect(...\Leaf\Config::getStatic('response.redirect'));
        } else {
            $this->response->end($body ?? '');
        }

        // $this->forceStateReset();
    }
}
