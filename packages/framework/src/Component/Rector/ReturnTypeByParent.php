<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ReturnTypeByParent extends AbstractRector
{
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private ReflectionResolver $reflectionResolver;

    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private StaticTypeMapper $staticTypeMapper;

    /**
     */
    public function __construct(
        ReflectionResolver $reflectionResolver,
        StaticTypeMapper $staticTypeMapper,
    ) {
        $this->reflectionResolver = $reflectionResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add method/function return type by annotation', [new CodeSample(
            <<<'CODE_SAMPLE'
TODO
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
TODO
CODE_SAMPLE
        )]);
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $node->name->name;

        if ($node->returnType !== null) {
            return null;
        }

        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }

        foreach ($classReflection->getAncestors() as $ancestorClassReflections) {
            if ($ancestorClassReflections === $classReflection) {
                continue;
            }
            $nativeClassReflection = $ancestorClassReflections->getNativeReflection();
            // this should avoid detecting @method as real method
            if (!$nativeClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethodReflection = $ancestorClassReflections->getNativeMethod($methodName);
            $parametersAcceptor = $parentClassMethodReflection->getVariants()[0];

            $parentReturnType = $parametersAcceptor->getNativeReturnType();
            if (!$parentReturnType instanceof MixedType || $parentReturnType->isExplicitMixed()) {
                $node->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parentReturnType, TypeKind::RETURN);

                return $node;
            }
        }

        return null;
    }
}
