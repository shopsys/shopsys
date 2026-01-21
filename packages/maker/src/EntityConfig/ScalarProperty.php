<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Heavily inspired by @see \Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassProperty
 */
class ScalarProperty extends Property
{
    /**
     * @param string $propertyName
     * @param string $type
     * @param \Shopsys\MakerBundle\EntityConfig\EntityTypeEnum $entityType
     * @param int|null $length
     * @param bool|null $nullable
     * @param array $options
     * @param int|null $precision
     * @param int|null $scale
     * @param bool $unique
     * @param string|null $enumType
     */
    public function __construct(
        public string $propertyName,
        public string $type,
        public EntityTypeEnum $entityType = EntityTypeEnum::ENTITY,
        public ?int $length = null,
        public ?bool $nullable = null,
        public array $options = [],
        public ?int $precision = null,
        public ?int $scale = null,
        public bool $unique = false,
        public ?string $enumType = null,
    ) {
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getAttributeLines(): array
    {
        $options = [];

        $options[] = sprintf("type: '%s'", $this->type);

        if (is_numeric($this->length)) {
            $options[] = sprintf('length: %d', $this->length);
        }

        if (is_numeric($this->precision)) {
            $options[] = sprintf('precision: %d', $this->precision);
        }

        if (is_numeric($this->scale)) {
            $options[] = sprintf('scale: %d', $this->scale);
        }

        if ($this->nullable) {
            $options[] = 'nullable: true';
        }

        if ($this->unique) {
            $options[] = 'unique: true';
        }

        // Handling additional 'options' array (e.g., 'default', 'comment')
        $optionStrings = [];

        foreach ($this->options as $key => $value) {
            if (is_string($value)) {
                $optionStrings[] = sprintf("'%s' => '%s'", $key, addslashes($value));
            } elseif (is_bool($value)) {
                $optionStrings[] = sprintf("'%s' => %s", $key, $value ? 'true' : 'false');
            } elseif (is_numeric($value)) {
                $optionStrings[] = sprintf("'%s' => %d", $key, $value);
            }
        }

        if (count($optionStrings) > 0) {
            $options[] = sprintf('options: [%s]', implode(', ', $optionStrings));
        }

        return [sprintf('#[ORM\Column(%s)]', implode(', ', $options))];
    }

    /**
     * @param \Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum $collectionTypeHintType
     * @return string
     */
    #[Override]
    public function getTypeHint(
        CollectionTypeHintTypeEnum $collectionTypeHintType = CollectionTypeHintTypeEnum::COLLECTION,
    ): string {
        $typeMapping = [
            'string' => ['string', 'ascii_string', 'text', 'guid', 'enum'],
            'bool' => ['boolean'],
            'int' => ['integer', 'smallint', 'bigint'],
            'float' => ['float', 'decimal'],
            '\\' . Money::class => ['money'],
            'array' => ['array', 'simple_array', 'json'],
            'object' => ['object'],
            '\\' . DateTime::class => ['datetime', 'datetimetz', 'date', 'time'],
            '\\' . DateTimeImmutable::class => ['datetime_immutable', 'datetimetz_immutable', 'date_immutable', 'time_immutable'],
            '\\' . DateInterval::class => ['dateinterval'],
        ];

        foreach ($typeMapping as $phpType => $ormTypes) {
            if (in_array($this->type, $ormTypes, true)) {
                return sprintf('%s%s', $this->nullable ? '?' : '', $phpType);
            }
        }

        return 'mixed';
    }

    /**
     * @return string
     */
    #[Override]
    public function getGetterName(): string
    {
        $prefix = $this->type === 'boolean' ? 'is' : 'get';

        return $prefix . ucfirst($this->propertyName);
    }

    /**
     * @return bool
     */
    #[Override]
    public function isCollection(): bool
    {
        return false;
    }
}
