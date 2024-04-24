<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

class HashGenerator
{
    protected string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * There are missing characters 0 and O
     */
    public string $charactersWithoutConfusingCharacters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789abcdefghijklmnpqrstuvwxyz';

    /**
     * @param int $length
     * @return string
     */
    public function generateHash($length)
    {
        $numberOfChars = strlen($this->characters);

        $hash = '';

        for ($i = 1; $i <= $length; $i++) {
            $randomIndex = random_int(0, $numberOfChars - 1);
            $hash .= $this->characters[$randomIndex];
        }

        return $hash;
    }

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
