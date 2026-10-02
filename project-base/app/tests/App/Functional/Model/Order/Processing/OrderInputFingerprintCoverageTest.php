<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Processing;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\App\Test\FunctionalTestCase;

final class OrderInputFingerprintCoverageTest extends FunctionalTestCase
{
    private const int BASELINE_ID = 1;
    private const int CHANGED_ID = 2;

    /**
     * @inject
     */
    private OrderInputFactory $orderInputFactory;

    public function testEveryPropertyOfOrderInputTakesPartInTheFingerprint(): void
    {
        $this->assertEveryPropertyTakesPartInTheFingerprintData($this->createOrderInput()::class);
    }

    public function testEveryPropertyOfQuantifiedProductTakesPartInTheFingerprint(): void
    {
        $orderInput = $this->createOrderInput();
        $orderInput->addProduct($this->createObjectWithId(Product::class, self::BASELINE_ID), 1);

        $this->assertEveryPropertyTakesPartInTheFingerprintData(array_first($orderInput->getQuantifiedProducts())::class);
    }

    private function createOrderInput(): OrderInput
    {
        return $this->orderInputFactory->create($this->domain->getCurrentDomainConfig());
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput|\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct> $className
     */
    private function assertEveryPropertyTakesPartInTheFingerprintData(string $className): void
    {
        $properties = $this->getInstanceProperties($className);
        $this->assertNotEmpty($properties);

        foreach ($properties as $changedProperty) {
            $this->assertNotSame(
                $this->getFingerprintDataOfInstance($className, $properties, null),
                $this->getFingerprintDataOfInstance($className, $properties, $changedProperty),
                sprintf('Property %s::$%s does not take part in the fingerprint, add it to %s::getFingerprintData()', $className, $changedProperty->getName(), $className),
            );
        }
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput|\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct> $className
     * @param \ReflectionProperty[] $properties
     */
    private function getFingerprintDataOfInstance(
        string $className,
        array $properties,
        ?ReflectionProperty $changedProperty,
    ): string {
        $instance = new ReflectionClass($className)->newInstanceWithoutConstructor();

        foreach ($properties as $property) {
            $property->setValue($instance, $this->createPropertyValue($property, $property === $changedProperty));
        }

        if ($instance instanceof OrderInput) {
            return $instance->getFingerprint();
        }

        return json_encode($instance->getFingerprintData(), JSON_THROW_ON_ERROR);
    }

    /**
     * @param class-string $className
     * @return \ReflectionProperty[]
     */
    private function getInstanceProperties(string $className): array
    {
        $properties = [];

        for ($class = new ReflectionClass($className); $class !== false; $class = $class->getParentClass()) {
            foreach ($class->getProperties() as $property) {
                if (!$property->isStatic() && !array_key_exists($property->getName(), $properties)) {
                    $properties[$property->getName()] = $property;
                }
            }
        }

        return array_values($properties);
    }

    private function createPropertyValue(ReflectionProperty $property, bool $changed): mixed
    {
        $id = $changed ? self::CHANGED_ID : self::BASELINE_ID;
        $type = $property->getType();

        if (!$type instanceof ReflectionNamedType) {
            $this->fail(sprintf('Property $%s has no single named type, extend the test to build its values', $property->getName()));
        }

        return match ($type->getName()) {
            'int' => $id,
            'float' => (float)$id,
            'string' => (string)$id,
            'bool' => $changed,
            'array' => $this->createArrayPropertyValue($property, $changed),
            default => $this->createObjectWithId($type->getName(), $id),
        };
    }

    /**
     * @return array<int|string, mixed>
     */
    private function createArrayPropertyValue(ReflectionProperty $property, bool $changed): array
    {
        if (!$changed) {
            return [];
        }

        if (preg_match('~@var\s+\\\\?([\w\\\\]+)\[\]~', (string)$property->getDocComment(), $matches) === 1) {
            return [$this->createObjectWithId($matches[1], self::CHANGED_ID)];
        }

        return ['fingerprintCoverage' => self::CHANGED_ID];
    }

    /**
     * @param class-string $className
     */
    private function createObjectWithId(string $className, int $id): object
    {
        if ($className === QuantifiedProduct::class) {
            return new QuantifiedProduct($this->createObjectWithId(Product::class, $id), $id);
        }

        $stub = $this->createStub($className);
        $stub->method('getId')->willReturn($id);

        return $stub;
    }
}
