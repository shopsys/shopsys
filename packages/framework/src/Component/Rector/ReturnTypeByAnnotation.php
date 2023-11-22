<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ReturnTypeByAnnotation extends AbstractRector
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private StaticTypeMapper $staticTypeMapper;

    /**
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver
     */
    private ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver;

    /**
     */
    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver,
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
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
        if ($node->returnType === null && $methodName !== '__construct') {
            if ($this->classMethodReturnVendorLockResolver->isVendorLocked($node)) {
                return null;
            }

            /** @var \PHPStan\Analyser\MutatingScope $scope */
            $scope = $node->getAttribute('scope');
            /** @var \PHPStan\Reflection\ClassReflection $classReflection */
            $classReflection = $scope->getClassReflection();
            /** @var \PHPStan\Reflection\Php\PhpMethodReflection $methodReflection */
            $methodReflection = $classReflection->getMethod($methodName, $scope);

            $returnType = $methodReflection->getVariants()[0]->getReturnType();

            if (!$returnType instanceof \PHPStan\Type\MixedType) {
                $node->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);

                return $node;
            }
        }

        return null;
    }
}
