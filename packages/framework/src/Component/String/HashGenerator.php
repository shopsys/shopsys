<?php

namespace Shopsys\FrameworkBundle\Component\String;

class HashGenerator
{
    private $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * @param int $length
     */
    public function generateHash($length): string
    {
        $numberOfChars = strlen($this->characters);

        $hash = '';
        for ($i = 1; $i <= $length; $i++) {
            $randomIndex = random_int(0, $numberOfChars - 1);
            $hash .= $this->characters[$randomIndex];
        }

        return $hash;
    }
}
