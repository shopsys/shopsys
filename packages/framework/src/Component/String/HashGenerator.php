<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

class HashGenerator
{
    protected const string CHARACTERS_WITH_SPECIAL_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()_+}{|":?><';
    protected const int STRONG_PASSWORD_LENGTH = 20;

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

    /**
     * @return string
     */
    public static function generateStrongPassword(): string
    {
        $numberOfChars = strlen(self::CHARACTERS_WITH_SPECIAL_CHARACTERS);

        $hash = '';

        for ($i = 1; $i <= self::STRONG_PASSWORD_LENGTH; $i++) {
            $randomIndex = random_int(0, $numberOfChars - 1);
            $hash .= self::CHARACTERS_WITH_SPECIAL_CHARACTERS[$randomIndex];
        }

        return $hash;
    }
}
