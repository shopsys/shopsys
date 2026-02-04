<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class ParameterFilter extends Constraint
{
    public const VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR = '83a312e6-4433-494d-9687-00d4a4908432';
    public const MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR = '06d34255-50cb-44ad-b07f-2d643962ad04';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR => 'VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR',
        self::MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR => 'MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $valuesNotSupportedForSliderTypeMessage
     * @param string $minMaxNotSupportedForNonSliderTypeMessage
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $valuesNotSupportedForSliderTypeMessage = 'An array of values is not supported as an input for "%type%" type parameter. Use "%minimalValue%" and "%maximalValue%" instead.',
        public string $minMaxNotSupportedForNonSliderTypeMessage = 'Minimal and maximal value are not supported as an input for other than "%type%" type parameter. Use "%values%" instead.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (is_array($options)) {
            DeprecationHelper::trigger(
                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
                static::class,
            );
        }

        parent::__construct($options, $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
