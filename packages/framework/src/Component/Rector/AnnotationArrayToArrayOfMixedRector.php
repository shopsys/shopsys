<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class AnnotationArrayToArrayOfMixedRector extends AbstractRector
{
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
        PhpDocInfoFactory $phpDocInfoFactory,
        DocBlockUpdater $docBlockUpdater,
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Property::class];
    }

    /**
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace all array annotation to mixed[]', [new CodeSample(
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
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     * @return \PhpParser\Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $changed = false;
        $phpDoc = $node->getDocComment();
        if ($phpDoc === null) {
            return null;
        }

        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode[] $children */
        $children = $propertyPhpDocInfo->getPhpDocNode()->children;
        foreach ($children as $child) {
            if ($child->value->type->name === 'array') {
                $child->value->type = new IdentifierTypeNode('mixed[]');
                $changed = true;
            }
        }

        if (!$changed) {
            return null;
        }

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
