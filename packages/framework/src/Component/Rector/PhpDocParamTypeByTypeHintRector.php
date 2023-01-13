<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer;
use Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer\SplFixedArrayParamTypeInferer;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class PhpDocParamTypeByTypeHintRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    protected $vendorLockResolver;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    protected $paramTypeInferer;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer
     */
    protected $traitTypeAnalyzer;

    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    protected $parentClassMethodTypeOverrideGuard;

    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer
     */
    protected $controllerRenderMethodAnalyzer;

    protected RectorUnionTypeHelper $rectorUnionTypeHelper;

    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer\SplFixedArrayParamTypeInferer
     */
    private SplFixedArrayParamTypeInferer $splFixedArrayParamTypeInferer;

    /**
     * @var \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower
     */
    private SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower;

    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private PhpDocTypeChanger $phpDocTypeChanger;

    /**
     * @param \Rector\VendorLocker\VendorLockResolver $vendorLockResolver
     * @param \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer $paramTypeInferer
     * @param \Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer $traitTypeAnalyzer
     * @param \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard
     * @param \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer $controllerRenderMethodAnalyzer
     * @param \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer\SplFixedArrayParamTypeInferer $splFixedArrayParamTypeInferer
     * @param \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower
     * @param \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger
     */
    public function __construct(
        VendorLockResolver $vendorLockResolver,
        ParamTypeInferer $paramTypeInferer,
        TraitTypeAnalyzer $traitTypeAnalyzer,
        ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard,
        ControllerRenderMethodAnalyzer $controllerRenderMethodAnalyzer,
        SplFixedArrayParamTypeInferer $splFixedArrayParamTypeInferer,
        SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower,
        PhpDocTypeChanger $phpDocTypeChanger,
    ) {
        $this->vendorLockResolver = $vendorLockResolver;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->traitTypeAnalyzer = $traitTypeAnalyzer;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->controllerRenderMethodAnalyzer = $controllerRenderMethodAnalyzer;
        $this->rectorUnionTypeHelper = new RectorUnionTypeHelper();
        $this->splFixedArrayParamTypeInferer = $splFixedArrayParamTypeInferer;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        // why not on Param node? because class like docblock is edited too for @param tags
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @param types to type declarations if typehint says something different', [new CodeSample(
            <<<'CODE_SAMPLE'
final class Foo
{
    /**
     * @param mixed $number
     */
    public function change(int $number)
    {
    }
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
final class Foo
{
    /**
     * @param int $number
     */
    public function change(int $number)
    {
    }
    /**
     * @param int $number
     */
    public function keep($number)
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
        $hasChanged = false;
        if ($node->params === []) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            if ($this->shouldSkipParam($param, $node)) {
                continue;
            }

            if ($this->refactorParam($param, $position, $node)) {
                $hasChanged = true;
            }

            if (false) {
                $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
                $inferedType = $functionLikePhpDocInfo->getParamType($param->var->name);
                $inferedType = RectorUnionTypeHelper::optimizeUnionType($inferedType);

                if (!$inferedType instanceof MixedType || $inferedType->isExplicitMixed()) {
                    $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::PARAM);
                    $param->type = $paramTypeNode;
                    $hasChanged = true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }

    /**
     * @return int
     */
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }

    /**
     * @param \PhpParser\Node\Param $param
     * @param int $position
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return bool
     */
    protected function refactorParam(Param $param, int $position, ClassMethod|Function_ $functionLike): bool
    {
        if ($param->type instanceof Node\UnionType) {
            $types = [];
            foreach ($param->type->types as $subType) {
                $subParamType = $this->nodeTypeResolver->getType($subType);
                $types[] = $this->splArrayFixedTypeNarrower->narrow($subParamType);
            }
            $paramTypeHint = new UnionType($types);
        } else {
            $paramTypeHint = $this->splFixedArrayParamTypeInferer->inferParam($param);
        }

        if ($paramTypeHint instanceof MixedType && !$paramTypeHint->isExplicitMixed()) {
            return false;
        }

        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramPhpDocType = $functionLikePhpDocInfo->getParamType($param->var->name);

        if ($paramPhpDocType->isSuperTypeOf($paramTypeHint)->yes() && !$paramPhpDocType->equals($paramTypeHint)) {
            // TODO: consider default value

            $this->phpDocTypeChanger->changeParamType($functionLikePhpDocInfo, $paramTypeHint, $param, $param->var->name);
            return true;
        }

        return false;
    }

    /**
     * @param \PhpParser\Node\Param $param
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return bool
     */
    protected function shouldSkipParam(Param $param, ClassMethod|Function_ $functionLike): bool
    {
        if ($param->variadic) {
            return true;
        }
        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike)) {
            return true;
        }
        if ($param->type === null) {
            return true;
        }

        // TODO: skip overriding methods

        return false;
    }
}
