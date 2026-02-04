<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Override;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Shopsys\FrameworkBundle\Component\Translation\Exception\StringValueUnextractableException;
use SplFileInfo;
use Symfony\Component\Validator\Constraint;
use Twig\Node\Node as TwigNode;

/**
 * Extracts messages from public properties (with names ending "message") of custom constraints for translation.
 *
 * Supports both regular properties and PHP 8 constructor-promoted properties:
 *
 *     // Regular properties:
 *     class MyConstraint extends Constraint
 *     {
 *         public $message = 'This value will be extracted.';
 *         public $otherMessage = 'This value will also be extracted.';
 *     }
 *
 *     // Constructor-promoted properties:
 *     class MyConstraint extends Constraint
 *     {
 *         public function __construct(
 *             public string $message = 'This will also be extracted.',
 *         ) {}
 *     }
 */
class ConstraintMessagePropertyExtractor implements FileVisitorInterface, NodeVisitor
{
    use FrontendApiValidationTranslationDomainDetectorTrait;

    protected NodeTraverser $traverser;

    protected MessageCatalogue $catalogue;

    protected SplFileInfo $file;

    protected ?bool $isInsideConstraintClass = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\PhpParserNodeHelper $phpParserNodeHelper
     */
    public function __construct(
        protected readonly PhpParserNodeHelper $phpParserNodeHelper,
    ) {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function enterNode(Node $node): int|Node|null
    {
        if ($node instanceof Class_) {
            $this->isInsideConstraintClass = $this->isConstraintClass($node);
            $this->setIfFrontendApiClass((string)$node->namespacedName);
        }

        if ($node instanceof Property && $node->isPublic() && $this->isInsideConstraintClass) {
            $this->extractMessagesFromProperty($node);
        }

        if ($node instanceof ClassMethod && $node->name->name === '__construct' && $this->isInsideConstraintClass) {
            $this->extractMessagesFromPromotedProperties($node);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function leaveNode(Node $node): int|Node|null
    {
        if ($node instanceof Class_) {
            $this->isInsideConstraintClass = false;
            $this->resetDetection();
        }

        return null;
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     * @return bool
     */
    protected function isConstraintClass(Class_ $node): bool
    {
        return is_subclass_of((string)$node->namespacedName, Constraint::class);
    }

    /**
     * @param \PhpParser\Node\Stmt\Property $node
     */
    protected function extractMessagesFromProperty(Property $node): void
    {
        foreach ($node->props as $propertyProperty) {
            if ($this->isMessageProperty($propertyProperty)) {
                $messageId = $this->phpParserNodeHelper->getConcatenatedStringValue($propertyProperty->default, $this->file);

                $message = new Message($messageId, $this->getTranslationDomain());
                $message->addSource(new FileSource($this->file->getFilename(), $propertyProperty->getLine()));

                $this->catalogue->add($message);
            }
        }
    }

    /**
     * @param \PhpParser\Node\PropertyItem $node
     * @return bool
     */
    protected function isMessageProperty(PropertyItem $node): bool
    {
        return $this->isMessage($node->name->toString());
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    protected function extractMessagesFromPromotedProperties(ClassMethod $node): void
    {
        foreach ($node->params as $param) {
            if (!$this->isPublicPromotedMessageParam($param)) {
                continue;
            }

            if ($param->default === null) {
                continue;
            }

            try {
                $messageId = $this->phpParserNodeHelper->getConcatenatedStringValue($param->default, $this->file);
            } catch (StringValueUnextractableException) {
                continue;
            }

            $message = new Message($messageId, $this->getTranslationDomain());
            $message->addSource(new FileSource($this->file->getFilename(), $param->getLine()));

            $this->catalogue->add($message);
        }
    }

    /**
     * @param \PhpParser\Node\Param $param
     * @return bool
     */
    protected function isPublicPromotedMessageParam(Param $param): bool
    {
        $isPublic = ($param->flags & Modifiers::PUBLIC) !== 0;
        $isMessageParam = $this->isMessage($param->var->name);

        return $isPublic && $isMessageParam;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast): null
    {
        return null;
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    protected function isMessage(string $propertyName): bool
    {
        return strtolower(substr($propertyName, -7)) === 'message';
    }
}
