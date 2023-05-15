<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\DocBlockParserAmbiguousTagException;

class DocBlockParser
{
    protected DocBlockFactory $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * @param string $docBlock
     * @return \phpDocumentor\Reflection\Type[]
     */
    public function getReturnTypes(string $docBlock): array
    {
        if ($docBlock === '') {
            return [];
        }

        $tags = $this->docBlockFactory->create($docBlock)->getTagsWithTypeByName('return');

        return array_map(static fn (TagWithType $tag) => $tag->getType(), $tags);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter $reflectionParameter
     * @return \phpDocumentor\Reflection\Type|null
     */
    public function getParameterType(ReflectionParameter $reflectionParameter): ?Type
    {
        $docBlock = $reflectionParameter->getDeclaringFunction()->getDocComment();

        if ($docBlock === '') {
            return null;
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param[] $functionParamTags */
        $functionParamTags = $this->docBlockFactory
            ->create($docBlock)
            ->getTagsWithTypeByName('param');

        /** @var \phpDocumentor\Reflection\Type|null $paramType */
        $paramType = null;

        foreach ($functionParamTags as $tag) {
            if ($tag->getVariableName() === $reflectionParameter->getName()) {
                $paramType = $tag->getType();
            }
        }

        return $paramType;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @return \phpDocumentor\Reflection\Type|null
     */
    public function getPropertyType(ReflectionProperty $reflectionProperty): ?Type
    {
        $docBlock = $reflectionProperty->getDocComment();

        if ($docBlock === '') {
            return null;
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_[] $propertyVarTags */
        $propertyVarTags = $this->docBlockFactory
            ->create($docBlock)
            ->getTagsByName('var');

        if (count($propertyVarTags) > 1) {
            $filePath = sprintf(
                '%s::$%s',
                $reflectionProperty->getImplementingClass()->getName(),
                $reflectionProperty->getName(),
            );

            throw new DocBlockParserAmbiguousTagException('@var', $filePath);
        }

        if (!isset($propertyVarTags[0])) {
            return null;
        }

        return $propertyVarTags[0]->getType();
    }
}
