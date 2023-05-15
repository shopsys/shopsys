<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

class ParameterView
{
    /**
     * @param int $id
     * @param string $name
     * @param int $valueId
     * @param string $valueText
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly int $valueId,
        protected readonly string $valueText,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getValueId(): int
    {
        return $this->valueId;
    }

    /**
     * @return string
     */
    public function getValueText(): string
    {
        return $this->valueText;
    }
}
