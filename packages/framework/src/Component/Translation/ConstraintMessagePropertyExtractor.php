<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
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
    protected NodeTraverser $traverser;

    protected MessageCatalogue $catalogue;

    protected SplFileInfo $file;

    protected ?bool $isInsideConstraintClass = null;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this);
    }

    /**
     * {@inheritdoc}
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node): int|Node|null
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
     * {@inheritdoc}
     */
    public function leaveNode(Node $node): int|Node|null
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
    protected function isConstraintClass(Class_ $node)
    {
        return is_subclass_of((string)$node->namespacedName, Constraint::class);
    }

    /**
     * @param \PhpParser\Node\Stmt\Property $node
     */
    protected function extractMessagesFromProperty(Property $node)
    {
        foreach ($node->props as $propertyProperty) {
            if ($this->isMessagePropertyProperty($propertyProperty)) {
                $messageId = PhpParserNodeHelper::getConcatenatedStringValue($propertyProperty->default, $this->file);

                $message = new Message($messageId, Translator::VALIDATOR_TRANSLATION_DOMAIN);
                $message->addSource(new FileSource($this->file->getFilename(), $propertyProperty->getLine()));

                $this->catalogue->add($message);
            }
        }
    }

    /**
     * @param \PhpParser\Node\PropertyItem $node
     * @return bool
     */
    protected function isMessagePropertyProperty(PropertyItem $node)
    {
        return strtolower(substr($node->name->toString(), -7)) === 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast)
    {
        return null;
    }
}
