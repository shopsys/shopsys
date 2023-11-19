<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use Doctrine\Common\DataFixtures\FixtureInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\MixedType;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class GuessReturnTypeByImplementationRector extends AbstractRector
{
    public function __construct(
        protected readonly StaticTypeMapper $staticTypeMapper,
        protected readonly PhpDocInfoFactory $phpDocInfoFactory,
        protected readonly PhpDocTypeChanger $phpDocTypeChanger
    ) {
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
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $node->name->name;

        if ($methodName === '__construct') {
            return null;
        }

        /** @var \PHPStan\Analyser\MutatingScope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();

        $methodReflection = $classReflection->getMethod($methodName, $scope);
        $parametersAcceptor = $methodReflection->getVariants()[0];
        $returnDocType = $parametersAcceptor->getPhpDocReturnType();
        $returnPhpType = $parametersAcceptor->getNativeReturnType();

        if (!$returnDocType instanceof MixedType || !$returnDocType->isExplicitMixed()) {
            $returnType = $returnPhpType->isSuperTypeOf($returnDocType)->yes() ? $returnDocType : $returnPhpType;
        } else {
            $returnType = $returnPhpType;
        }
        if ($returnPhpType instanceof VoidType || !$this->isTooCommonType($returnType)) {
            return null;
        }

        $detectedReturnTypes = [];
        $this->traverseNodesWithCallable($node->stmts, function (Node $methodContentNode) use ($scope, $node, &$detectedReturnTypes) {
            if ($methodContentNode instanceof Node\Stmt\Return_ && $methodContentNode->expr instanceof Node\Expr) {
                $detectedType = $scope->getType($methodContentNode->expr);
                // prevent type 'never[]'
                if ($detectedType instanceof ArrayType && $detectedType->getItemType() instanceof NeverType) {
                    $detectedType = new ArrayType($detectedType->getKeyType(), new MixedType());
                }

                // UnionType doesn't make sense, because final return type makes UnionType if it is needed
                if ($detectedType instanceof UnionType) {
                    foreach ($detectedType->getTypes() as $unionSubType) {
                        $detectedReturnTypes[] = $unionSubType;
                    }
                } else {
                    $detectedReturnTypes[] = $detectedType;
                }
            }
        });

        if (count($detectedReturnTypes) === 0) {
            return null;
        }

        if (count($detectedReturnTypes) === 1) {
            $detectedReturnType = reset($detectedReturnTypes);
        } else {
            $detectedReturnType = new UnionType($detectedReturnTypes);
        }

        if ($detectedReturnType->isSuperTypeOf($returnType)->yes()) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);


        if ($this->isTooComplex($detectedReturnType, 10)) {
            $detectedReturnType = $this->simplifyTooComplex($classReflection, $detectedReturnType, 15, 10);
            // Guessed return type is often too complex and contains duplications,
            // so we need to simplify and deduplicate via PhpDoc (it does not work on same Node)
            $dummyNode = new ClassMethod('dummyNode');
            $dummyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($dummyNode);
            $this->phpDocTypeChanger->changeReturnType($dummyNode, $dummyPhpDocInfo, $detectedReturnType);
            $detectedReturnType = $dummyPhpDocInfo->getReturnType();
        }

        $detectedReturnType = $this->simplifyTooComplex($classReflection, $detectedReturnType, 10, 5);

        $node->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($detectedReturnType, TypeKind::RETURN);
        $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $detectedReturnType);

        return $node;
    }

    protected function simplifyTooComplex(
        ClassReflection $classReflection,
        Type $type,
        int $commonClassThreshold,
        int $wordyClassThreshold
    ): Type {
        $maximumComplexity = $commonClassThreshold;
        if ($classReflection->isSubclassOf(TestCase::class)
            || $classReflection->implementsInterface(FixtureInterface::class)
        ) {
            $maximumComplexity = $wordyClassThreshold;
        }

        if ($this->isTooComplex($type, $maximumComplexity)) {
            return $type->generalize(GeneralizePrecision::templateArgument());
        }

        return $type;
    }

    protected function isTooComplex(Type $type, int $maxNodes, int &$currentNodeCount = 1): bool
    {
        if ($currentNodeCount >= $maxNodes) {
            return true;
        }

        if (get_class($type) === UnionType::class) {
            foreach ($type->getTypes() as $unionSubType) {
                $currentNodeCount++;
                if ($this->isTooComplex($unionSubType, $maxNodes, $currentNodeCount)) {
                    return true;
                }
            }
        }

        if (get_class($type) === ArrayType::class) {
            $currentNodeCount++;
            if ($this->isTooComplex($type->getItemType(), $maxNodes, $currentNodeCount)) {
                return true;
            }
        }

        if (get_class($type) === ConstantArrayType::class) {
            foreach ($type->getKeyTypes() as $keyType) {
                if ($keyType instanceof ConstantStringType) {
                    if (strlen($keyType->getValue()) > 15) {
                        return true;
                    }
                    $currentNodeCount += 3; // extra penalty
                }

                $currentNodeCount++;
                if ($this->isTooComplex($keyType, $maxNodes, $currentNodeCount)) {
                    return true;
                }
            }
        }

        return $currentNodeCount >= $maxNodes;
    }

    protected function isTooCommonType(Type $type): bool
    {
        return $type instanceof MixedType
            || ($type instanceof ArrayType
                && $type->getKeyType() instanceof MixedType
                && $type->getItemType() instanceof MixedType
            );
    }
}
