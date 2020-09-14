<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Model\Customer\User\CustomerUser;

class ScontoBridgeTitleResolver
{
    public const INDIVIDUAL_TITLE_MALE = 0;
    public const INDIVIDUAL_TITLE_FEMALE = 1;
    private const TITLE_GENDER = [
        self::INDIVIDUAL_TITLE_FEMALE => CustomerUser::GENDER_FEMALE,
        self::INDIVIDUAL_TITLE_MALE => CustomerUser::GENDER_MALE,
    ];

    /**
     * @param int $individualTitle
     * @return string|null
     */
    public function getGenderByIndividualTitle(int $individualTitle): ?string
    {
        if (array_key_exists($individualTitle, self::TITLE_GENDER) === false) {
            return null;
        }

        return self::TITLE_GENDER[$individualTitle];
    }

    /**
     * @param string $gender
     * @return int|null
     */
    public function getIndividualTitleByGender(string $gender): ?int
    {
        $genderTitle = array_flip(self::TITLE_GENDER);
        if (array_key_exists($gender, $genderTitle) === false) {
            return null;
        }

        return $genderTitle[$gender];
    }
}
