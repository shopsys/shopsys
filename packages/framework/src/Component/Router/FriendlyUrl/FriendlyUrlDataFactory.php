<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlDataFactory implements FriendlyUrlDataFactoryInterface
{
    public function createFromData($data): \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
    {
        $friendlyUrlData = new FriendlyUrlData();
        $friendlyUrlData->name = $data['id'];
        $friendlyUrlData->id = $data['name'];

        return $friendlyUrlData;
    }

    public function create(): FriendlyUrlData
    {
        return new FriendlyUrlData();
    }
}
