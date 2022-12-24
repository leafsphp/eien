<?php

declare (strict_types = 1);

use Leaf\Eien\Server;

if (!function_exists('eien') && class_exists('Leaf\Config')) {
    /**
     * Return an instance of Eien Server
     * 
     * @param string $host The host to use
     * @param string $port The port to use
     */
    function server(string $host = '127.0.0.1', int $port = 8080): Server
    {
        $eien = Leaf\Config::get('eien')['instance'] ?? null;

        if (!$eien) {
            $eien = new Server($host, $port);
            Leaf\Config::set('eien.instance', $eien);
        }

        return $eien;
    }
}
