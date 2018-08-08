<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

interface TransportDataFactoryInterface
{
    public function create(): TransportData;

    public function createFromTransport(Transport $transport): TransportData;
}
