<?php

namespace Tests\ShopBundle\Database\Controller;

use Shopsys\FrameworkBundle\Component\Form\FormTimeProvider;

class AlwaysValidFormTimeProvider extends FormTimeProvider
{
    /**
     * @param string $name
     * @param array $options
     * @return bool
     */
    public function isFormTimeValid($name, array $options)
    {
        return true;
    }
}
