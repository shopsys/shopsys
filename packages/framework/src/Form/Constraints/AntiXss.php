<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AntiXss extends Constraint
{
    public string $message = 'This field contains potentially dangerous content and cannot be processed.';

    public const ERROR_CODE = 'potential-xss';

    /**
     * @var string[]
     */
    public array $excludedFields = [];

    /**
     * @param mixed|null $options
     * @param string|null $message
     * @param array|null $excludedFields
     * @param array|null $groups
     * @param mixed|null $payload
     */
    public function __construct(
        mixed $options = null,
        ?string $message = null,
        ?array $excludedFields = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->excludedFields = $excludedFields ?? $this->excludedFields;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return [
            self::PROPERTY_CONSTRAINT,
            self::CLASS_CONSTRAINT,
        ];
    }
}
