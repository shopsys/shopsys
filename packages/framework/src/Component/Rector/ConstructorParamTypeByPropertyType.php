<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ConstructorParamTypeByPropertyType extends AbstractRector
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private StaticTypeMapper $staticTypeMapper;

    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private PhpDocInfoFactory $phpDocInfoFactory;

    /**
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private DocBlockUpdater $docBlockUpdater;

    /**
     */
    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        PhpDocInfoFactory $phpDocInfoFactory,
        DocBlockUpdater $docBlockUpdater,
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Class_::class];
    }

    /**
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update constructor parameter type by assigned property type', [new CodeSample(
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
     * @param \PhpParser\Node\Stmt\Class_ $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $constructor = $this->getConstructorMethod($node);
        if ($constructor === null) {
            return null;
        }

        $propertyNamesByParameterName = $this->getPropertyNamesByParameterName($constructor);

        $constructorPhpDocInfo = $this->phpDocInfoFactory->createFromNode($constructor);
        foreach ($constructor->getParams() as $param) {
            if ($param->type !== null) {
                continue;
            }

            $parameterName = $param->var->name;
            if (array_key_exists($parameterName, $propertyNamesByParameterName)) {
                foreach ($node->getProperties() as $propertyNode) {
                    if ($propertyNode->props[0]->name->name === $propertyNamesByParameterName[$parameterName]) {
                        $parameterPhpStanType = $this->nodeTypeResolver->getType($propertyNode);

                        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyNode);
                        $propertyPhpDocType = $propertyPhpDocInfo->getVarType();

                        if ((!$propertyPhpDocType instanceof MixedType || $propertyPhpDocType->isExplicitMixed()) && $parameterPhpStanType->isSuperTypeOf($propertyPhpDocType)->yes()) {
                            $parameterPhpStanType = $propertyPhpDocType;
                        }
                        $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterPhpStanType, TypeKind::PARAM);
                        $hasChanged = true;

                        $this->updateConstructorAnnotation($constructorPhpDocInfo, $parameterName, $parameterPhpStanType);
                        break;
                    }
                }
            }
        }

        if (!$hasChanged) {
            return null;
        }

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($constructor);

        return $node;
    }

    /**
     * @param \PHPStan\Type\Type $parameterPhpStanType
     * @return array|string|string[]|null
     */
    public function getPhpDocTypeText(\PHPStan\Type\Type $parameterPhpStanType): string|array|null
    {
        $typeDocText = $parameterPhpStanType->describe(VerbosityLevel::precise());
        $typeDocText = preg_replace('/array<(\\w+)>/', '${1}[]', $typeDocText);
        return $typeDocText;
    }

    /**
     * @param \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $constructorPhpDocInfo
     * @param string $parameterName
     * @param \PHPStan\Type\Type $parameterPhpStanType
     */
    public function updateConstructorAnnotation(
        PhpDocInfo $constructorPhpDocInfo,
        string $parameterName,
        Type $parameterPhpStanType
    ): void {
        foreach ($constructorPhpDocInfo->getPhpDocNode()->children as $children) {
            if ($children !== null) {
                if ($children->name === '@param' && $children->value->parameterName === '$' . $parameterName) {
                    $children->value->type = new IdentifierTypeNode($this->getPhpDocTypeText($parameterPhpStanType));
                    return;
                }
            }
        }

        $constructorPhpDocInfo->getPhpDocNode()->children[] = new PhpDocTagNode(
            '@param',
            new ParamTagValueNode(new IdentifierTypeNode($this->getPhpDocTypeText($parameterPhpStanType)),
                false,
                '$' . $parameterName,
                '',
                false
            ));
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $constructor
     * @return array<string, string>
     */
    public function getPropertyNamesByParameterName(ClassMethod $constructor): array
    {
        $propertyNamesByParameterName = [];

        foreach ($constructor->getStmts() as $constructorStmt) {
            $assign = $constructorStmt->expr;
            if (!$assign instanceof Node\Expr\Assign) {
                continue;
            }

            $assignedProperty = $assign->var;
            if (!$assignedProperty instanceof Node\Expr\PropertyFetch) {
                continue;
            }

            if ($assignedProperty->var->name !== 'this') {
                continue;
            }

            if (!$assign->expr instanceof Node\Expr\Variable) {
                continue;
            }
            $propertyNamesByParameterName[$assign->expr->name] = $assignedProperty->name->name;
        }

        return $propertyNamesByParameterName;
    }

    /**
     * @param \PhpParser\Node|\PhpParser\Node\Stmt\Class_ $node
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    public function getConstructorMethod(Node|Node\Stmt\Class_ $node): ?ClassMethod
    {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === '__construct') {
                return $method;
            }
        }

        return null;
    }
}
