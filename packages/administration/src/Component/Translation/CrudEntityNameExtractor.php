<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use SplFileInfo;
use Twig\Node\Node as TwigNode;

/**
 * Extracts singular and plural entity names from #[CrudController] attributes for translation.
 */
final class CrudEntityNameExtractor implements FileVisitorInterface, NodeVisitor
{
    private NodeTraverser $traverser;

    private MessageCatalogue $catalogue;

    private SplFileInfo $file;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
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
        if (!$node instanceof Class_ || $node->attrGroups === []) {
            return null;
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($this->isCrudControllerAttribute($attr)) {
                    $this->extractEntityNames($attr);
                }
            }
        }

        return null;
    }

    private function isCrudControllerAttribute(Attribute $attr): bool
    {
        return $attr->name instanceof FullyQualified
            && (string)$attr->name === CrudController::class;
    }

    private function extractEntityNames(Attribute $attr): void
    {
        $entityClass = $this->resolveEntityClass($attr);

        if ($entityClass === null || !class_exists($entityClass)) {
            return;
        }

        $entityName = (new ReflectionClass($entityClass))->getShortName();
        $singular = CrudTransformationHelper::toSingularEntityName($entityName);
        $plural = CrudTransformationHelper::toPluralEntityName($entityName);

        $this->addMessage($singular, $attr->getStartLine());
        $this->addMessage($plural, $attr->getStartLine());
    }

    private function resolveEntityClass(Attribute $attr): ?string
    {
        foreach ($attr->args as $arg) {
            $isEntityClassArg = ($arg->name !== null && $arg->name->name === 'entityClass')
                || ($arg->name === null);

            if ($isEntityClassArg && $arg->value instanceof Node\Expr\ClassConstFetch) {
                return (string)$arg->value->class;
            }
        }

        return null;
    }

    private function addMessage(string $messageId, int $line): void
    {
        $message = new Message($messageId);
        $message->addSource(new FileSource($this->file->getFilename(), $line));

        $this->catalogue->add($message);
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
    public function leaveNode(Node $node): int|Node|null
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
}
