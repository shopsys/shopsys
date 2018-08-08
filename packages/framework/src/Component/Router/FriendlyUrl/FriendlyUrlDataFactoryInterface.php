<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

interface FriendlyUrlDataFactoryInterface
{
    public function create(): FriendlyUrlData;
}
