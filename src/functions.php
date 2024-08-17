<?php

declare (strict_types = 1);

use Leaf\Eien\Server;

if (!function_exists('eien') && class_exists('Leaf\Config')) {
    /**
     * Return an instance of Eien Server
     * 
     * @param string $host The host to use
     * @param int $port The port to use
     */
    function server(string $host = '127.0.0.1', int $port = 9501): Server
    {
        if (!(\Leaf\Config::getStatic('eien'))) {
            \Leaf\Config::singleton('eien', function () {
                return new Server();
            });
        }

        return \Leaf\Config::get('eien');
    }
}
