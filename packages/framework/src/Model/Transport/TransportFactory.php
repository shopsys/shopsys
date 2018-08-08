<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportFactory implements TransportFactoryInterface
{

    public function create(TransportData $data): Transport
    {
        return new Transport($data);
    }
}
