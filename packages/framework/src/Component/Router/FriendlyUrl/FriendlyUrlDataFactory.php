<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlDataFactory implements FriendlyUrlDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function createFromData($data)
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
