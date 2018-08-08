<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

interface TransportFactoryInterface
{

    public function create(TransportData $data): Transport;
}
