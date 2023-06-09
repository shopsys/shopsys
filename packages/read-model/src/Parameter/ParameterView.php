<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

class ParameterView
{
    protected int $id;

    protected string $name;

    protected int $valueId;

    protected string $valueText;

    /**
     * @param int $id
     * @param string $name
     * @param int $valueId
     * @param string $valueText
     */
    public function __construct(
        int $id,
        string $name,
        int $valueId,
        string $valueText
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->valueId = $valueId;
        $this->valueText = $valueText;
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
