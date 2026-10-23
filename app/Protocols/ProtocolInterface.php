<?php

namespace App\Protocols;

interface ProtocolInterface
{
    /**
     * Run the protocol with given parameters.
     *
     * @param array $params
     * @return array
     */
    public function run(array $params = []): array;
}
