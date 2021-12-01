<?php

declare(strict_types=1);

namespace App\Component\String;

use Shopsys\FrameworkBundle\Component\String\HashGenerator as BaseHashGenerator;

class HashGenerator extends BaseHashGenerator
{
    /**
     * There is missing characters 0 and O
     */
    public $charactersWithoutConfusingCharacters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789abcdefghijklmnpqrstuvwxyz';

    /**
     * @param int $length
     * @return string
     */
    public function generateHashWithoutConfusingCharacters(int $length): string
    {
        $numberOfChars = strlen($this->charactersWithoutConfusingCharacters);

        $hash = '';
        for ($i = 1; $i <= $length; $i++) {
            $randomIndex = random_int(0, $numberOfChars - 1);
            $hash .= $this->charactersWithoutConfusingCharacters[$randomIndex];
        }

        return $hash;
    }
}
