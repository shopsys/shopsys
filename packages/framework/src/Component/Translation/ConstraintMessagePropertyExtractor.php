<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use SplFileInfo;
use Symfony\Component\Validator\Constraint;
use Twig\Node\Node as TwigNode;

/**
 * Extracts messages from public properties (with names ending "message") of custom constraints for translation.
 *
 * Example:
 *     class MyConstraint extends Constraint
 *     {
 *         public $message = 'This value will be extracted.';
 *
 *         public $otherMessage = 'This value will also be extracted.';
 *
 *         public $differentProperty = 'This value will not be extracted (not a message).';
 *     }
 */
class ConstraintMessagePropertyExtractor implements FileVisitorInterface, NodeVisitor
{
    /**
     * @var \PhpParser\NodeTraverser
     */
    protected $traverser;

    /**
     * @var \JMS\TranslationBundle\Model\MessageCatalogue
     */
    protected $catalogue;

    /**
     * @var \SplFileInfo
     */
    protected $file;

    /**
     * @var bool
     */
    protected $isInsideConstraintClass = false;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this);
    }

    /**
     * @param \SplFileInfo $file
     * @param \JMS\TranslationBundle\Model\MessageCatalogue $catalogue
     * @param \PhpParser\Node[] $ast
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node): null|int|Node
    {
        if ($node instanceof Class_) {
            $this->isInsideConstraintClass = $this->isConstraintClass($node);
        }

        if ($node instanceof Property && $node->isPublic() && $this->isInsideConstraintClass) {
            $this->extractMessagesFromProperty($node);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node): null|int|Node|array
    {
        if ($node instanceof Class_) {
            $this->isInsideConstraintClass = false;
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
            if ($this->isMessagePropertyProperty($propertyProperty)) {
                $messageId = PhpParserNodeHelper::getConcatenatedStringValue($propertyProperty->default, $this->file);

                $message = new Message($messageId, ConstraintMessageExtractor::CONSTRAINT_MESSAGE_DOMAIN);
                $message->addSource(new FileSource($this->file->getFilename(), $propertyProperty->getLine()));

                $this->catalogue->add($message);
            }
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\PropertyProperty $node
     * @return bool
     */
    protected function isMessagePropertyProperty(PropertyProperty $node): bool
    {
        return strtolower(substr($node->name, -7)) === 'message';
    }

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
    }

    /**
     * @inheritdoc
     */
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast): void
    {
    }
}
