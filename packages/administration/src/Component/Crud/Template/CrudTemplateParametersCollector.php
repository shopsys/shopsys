<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Template;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Webmozart\Assert\Assert;

/**
 * Collects the parameters passed to the template of a CRUD action.
 *
 * The base parameters of the action are extended by the additional parameters of the CRUD controller
 * and of each of its extensions. Every source is validated against the parameters collected so far,
 * so no parameter can be silently overwritten by another source.
 */
final class CrudTemplateParametersCollector
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var array<string, class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController|\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>|null> Source class of each collected parameter, null for the base parameters
     */
    private array $sourceClassByKey;

    /**
     * @param array<string, mixed> $baseParameters
     */
    public function __construct(
        private readonly ActionType $actionType,
        array $baseParameters,
    ) {
        $this->parameters = $baseParameters;
        $this->sourceClassByKey = array_fill_keys(array_keys($baseParameters), null);
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController|\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension> $sourceClass
     * @param array<string, mixed> $additionalParameters
     */
    public function addAdditionalParameters(string $sourceClass, array $additionalParameters): void
    {
        $collidingKeys = ArrayHelper::getCommonKeys($additionalParameters, $this->parameters);

        Assert::isEmpty($collidingKeys, fn (): string => sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "%s" action: %s. Rename the additional parameters.',
            $sourceClass,
            $this->actionType->value,
            $this->describeCollidingKeys($collidingKeys),
        ));

        $this->parameters = [...$this->parameters, ...$additionalParameters];
        $this->sourceClassByKey = [
            ...$this->sourceClassByKey,
            ...array_fill_keys(array_keys($additionalParameters), $sourceClass),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<int|string> $collidingKeys
     */
    private function describeCollidingKeys(array $collidingKeys): string
    {
        $descriptions = [];

        foreach ($collidingKeys as $collidingKey) {
            $sourceClass = $this->sourceClassByKey[$collidingKey];

            $descriptions[] = sprintf(
                '"%s" (%s)',
                $collidingKey,
                $sourceClass === null ? 'a base parameter' : sprintf('already added by "%s"', $sourceClass),
            );
        }

        return implode(', ', $descriptions);
    }
}
