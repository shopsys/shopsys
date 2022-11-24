<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInfererTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use const false;
use const true;

class ReturnTypeDeclarationByPhpDocRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    protected $returnTypeInferer;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    protected $returnTypeAlreadyAddedChecker;

    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    protected $classMethodReturnTypeOverrideGuard;

    /**
     * @readonly
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    protected $vendorLockResolver;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpParserTypeAnalyzer
     */
    protected $phpParserTypeAnalyzer;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator
     */
    protected $objectTypeComparator;

    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    protected $phpVersionProvider;

    /**
     * @param \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer
     * @param \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker
     * @param \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard
     * @param \Rector\VendorLocker\VendorLockResolver $vendorLockResolver
     * @param \Rector\TypeDeclaration\PhpParserTypeAnalyzer $phpParserTypeAnalyzer
     * @param \Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator $objectTypeComparator
     * @param \Rector\Core\Php\PhpVersionProvider $phpVersionProvider
     */
    public function __construct(ReturnTypeInferer $returnTypeInferer, ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, VendorLockResolver $vendorLockResolver, PhpParserTypeAnalyzer $phpParserTypeAnalyzer, ObjectTypeComparator $objectTypeComparator, PhpVersionProvider $phpVersionProvider)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->returnTypeAlreadyAddedChecker = $returnTypeAlreadyAddedChecker;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->phpParserTypeAnalyzer = $phpParserTypeAnalyzer;
        $this->objectTypeComparator = $objectTypeComparator;
        $this->phpVersionProvider = $phpVersionProvider;
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @return types and type from static analysis to type declarations if not a BC-break', [new CodeSample(
            <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int
     */
    public function getCount(): int
    {
    }
}
CODE_SAMPLE
        )]);
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassLike($node)) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->shouldSkipClassMethod($node)) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $inferedReturnType = $phpDocInfo->getReturnType();

        if ($inferedReturnType instanceof MixedType && !$inferedReturnType->isExplicitMixed()) {
            $inferedReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers($node, [ReturnTypeDeclarationReturnTypeInfererTypeInferer::class]);
        }
        $inferedReturnType = RectorUnionTypeHelper::optimizeUnionType($inferedReturnType);

        if ($inferedReturnType instanceof MixedType || $inferedReturnType instanceof NonExistingObjectType) {
            return null;
        }
        if ($this->returnTypeAlreadyAddedChecker->isSameOrBetterReturnTypeAlreadyAdded($node, $inferedReturnType)) {
            return null;
        }
        if (!$inferedReturnType instanceof UnionType) {
            return $this->processType($node, $inferedReturnType);
        }
        foreach ($inferedReturnType->getTypes() as $unionedType) {
            // mixed type cannot be joined with another types
            if ($unionedType instanceof MixedType) {
                return null;
            }
        }

        return $this->processType($node, $inferedReturnType);
    }

    /**
     * @return int
     */
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     * @param \PHPStan\Type\Type $inferedType
     * @return \PhpParser\Node|null
     */
    protected function processType($node, Type $inferedType): ?Node
    {
        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::RETURN);
        // nothing to change in PHP code
        if (!$inferredReturnNode instanceof Node) {
            return null;
        }
        if ($this->shouldSkipInferredReturnNode($node)) {
            return null;
        }
        // should be previous overridden?
        if ($node->returnType !== null && $this->shouldSkipExistingReturnType($node, $inferedType)) {
            return null;
        }
        $this->addReturnType($node, $inferredReturnNode);

        return $node;
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $classMethod
     * @return bool
     */
    protected function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return true;
        }
        return $this->vendorLockResolver->isReturnChangeVendorLockedIn($classMethod);
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return bool
     */
    protected function shouldSkipInferredReturnNode($functionLike): bool
    {
        // already overridden by previous populateChild() method run
        if ($functionLike->returnType === null) {
            return false;
        }
        return (bool)$functionLike->returnType->getAttribute(AttributeKey::DO_NOT_CHANGE);
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @param \PHPStan\Type\Type $inferedType
     * @return bool
     */
    protected function shouldSkipExistingReturnType($functionLike, Type $inferedType): bool
    {
        if ($functionLike->returnType === null) {
            return false;
        }
        if ($functionLike instanceof ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn($functionLike)) {
            return true;
        }
        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        if ($this->objectTypeComparator->isCurrentObjectTypeSubType($currentType, $inferedType)) {
            return true;
        }
        return $this->isNullableTypeSubType($currentType, $inferedType);
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\IntersectionType $inferredReturnNode
     */
    protected function addReturnType($functionLike, $inferredReturnNode): void
    {
        if ($functionLike->returnType === null) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        $isSubtype = $this->phpParserTypeAnalyzer->isCovariantSubtypeOf($inferredReturnNode, $functionLike->returnType);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        if (!$isSubtype) {
            // type override with correct one
            $functionLike->returnType = $inferredReturnNode;
        }
    }

    /**
     * @param \PHPStan\Type\Type $currentType
     * @param \PHPStan\Type\Type $inferedType
     * @return bool
     */
    protected function isNullableTypeSubType(Type $currentType, Type $inferedType): bool
    {
        if (!$currentType instanceof UnionType) {
            return false;
        }
        if (!$inferedType instanceof UnionType) {
            return false;
        }
        // probably more/less strict union type on purpose
        if ($currentType->isSubTypeOf($inferedType)->yes()) {
            return true;
        }
        return $inferedType->isSubTypeOf($currentType)->yes();
    }

    /**
     * @param \PhpParser\Node\FunctionLike $functionLike
     * @return bool
     */
    protected function shouldSkipClassLike(FunctionLike $functionLike): bool
    {
        if (!$functionLike instanceof ClassMethod) {
            return false;
        }
        $classLike = $this->betterNodeFinder->findParentType($functionLike, Class_::class);
        return !$classLike instanceof Class_;
    }
}
