<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeDumper;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MissingMixedAnnotationToArrayPropertiesRector extends AbstractRector
{
    public function __construct(
        protected readonly PhpDocInfoFactory $phpDocInfoFactory,
        protected readonly PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Property::class];
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
     * @param \PhpParser\Node\Stmt\Property $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $docComment = $node->getDocComment();
        if ($docComment !== null) {
            return null;
        }

        $resolvedType = $this->nodeTypeResolver->getType($node);
        if (!$resolvedType instanceof ArrayType) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTypeChanger->changeVarType(
            $node,
            $phpDocInfo,
            $resolvedType
        );

        return $node;

        /** @var \PHPStan\Analyser\MutatingScope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();

        $methodReflection = $classReflection->getMethod($methodName, $scope);
        $variants = $methodReflection->getVariants();
        $parameterReflections = $variants[0]->getParameters();
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach ($parameterReflections as $parameterReflection) {
            $phpDocType = $parameterReflection->getPhpDocType();
            if (!$phpDocType instanceof MixedType || !$phpDocType->isExplicitMixed()) {
                continue;
            }

            if ($phpDocType->isSuperTypeOf($parameterReflection->getType())->yes()) {
                foreach ($node->getParams() as $param) {
                    if ($param->var->name === $parameterReflection->getName()) {
                        $this->phpDocTypeChanger->changeParamType(
                            $node,
                            $phpDocInfo,
                            $parameterReflection->getType(),
                            $param,
                            $parameterReflection->getName()
                        );
                        $changed = true;
                        break;
                    }
                }
            }
        }

        if (!$changed) {
            return null;
        }

        return $node;
    }
}
