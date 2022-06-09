<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ParameterFilter extends Constraint
{
    public const VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR = '83a312e6-4433-494d-9687-00d4a4908432';
    public const MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR = '06d34255-50cb-44ad-b07f-2d643962ad04';

    public string $valuesNotSupportedForSliderTypeMessage = 'An array of values is not supported as an input for "slider" type parameter. Use "minimalValue" and "maximalValue" instead.';

    public string $minMaxNotSupportedForNonSliderTypeMessage = 'Minimal and maximal value are not supported as an input for other than "slider" type parameter. Use "values" instead.';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR => 'VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR',
        self::MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR => 'MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
