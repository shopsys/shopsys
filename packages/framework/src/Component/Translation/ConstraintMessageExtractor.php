<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;
use ReflectionException;
use Shopsys\FrameworkBundle\Component\Translation\Exception\StringValueUnextractableException;
use SplFileInfo;
use Symfony\Component\Validator\Constraint;
use Twig\Node\Node as TwigNode;

/**
 * Extracts custom messages from constraint constructors for translation.
 *
 * Examples:
 *     new Constraint\NotBlank(message: 'This message will be extracted into "validators" translation domain');
 *     new Constraint\Length(
 *         max: 50,
 *         minMessage: 'Actually, every option ending with "message" will be extracted',
 *     );
 */
class ConstraintMessageExtractor implements FileVisitorInterface, NodeVisitor
{
    use FrontendApiValidationTranslationDomainDetectorTrait;

    protected NodeTraverser $traverser;

    protected MessageCatalogue $catalogue;

    protected SplFileInfo $file;

    /**
     * @var array<string, array<int, string>>
     */
    protected array $constructorParameterNamesCache = [];

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
        if ($node instanceof Class_) {
            $this->setIfFrontendApiClass((string)$node->namespacedName);
        }

        if ($node instanceof New_) {
            if ($this->isConstraintClass($node->class) && count($node->args) > 0) {
                $this->extractMessagesFromArgs($node->args, (string)$node->class);
            }
        }

        return null;
    }

    /**
     * @param \PhpParser\Node $node
     * @return bool
     */
    protected function isConstraintClass(Node $node): bool
    {
        return $node instanceof FullyQualified && is_subclass_of((string)$node, Constraint::class);
    }

    /**
     * @param array<\PhpParser\Node\Arg|\PhpParser\Node\VariadicPlaceholder> $args
     * @param string $constraintClassName
     */
    protected function extractMessagesFromArgs(array $args, string $constraintClassName): void
    {
        foreach ($args as $position => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }

            if ($arg->name !== null) {
                $paramName = $arg->name->name;
            } elseif (isset($this->getConstructorParameterNames($constraintClassName)[$position])) {
                $paramName = $this->getConstructorParameterNames($constraintClassName)[$position];
            } else {
                continue;
            }

            if (!$this->isMessageParamName($paramName)) {
                continue;
            }

            try {
                $messageId = $this->phpParserNodeHelper->getConcatenatedStringValue($arg->value, $this->file);
            } catch (StringValueUnextractableException) {
                continue;
            }

            $message = new Message($messageId, $this->getTranslationDomain());
            $message->addSource(new FileSource($this->file->getFilename(), $arg->getLine()));

            $this->catalogue->add($message);
        }
    }

    /**
     * @param string $className
     * @return array<int, string> Parameter names indexed by position
     */
    protected function getConstructorParameterNames(string $className): array
    {
        if (array_key_exists($className, $this->constructorParameterNamesCache)) {
            return $this->constructorParameterNamesCache[$className];
        }

        try {
            $reflectionClass = new ReflectionClass($className);
            $constructor = $reflectionClass->getConstructor();

            if ($constructor === null) {
                $this->constructorParameterNamesCache[$className] = [];

                return $this->constructorParameterNamesCache[$className];
            }

            $names = [];

            foreach ($constructor->getParameters() as $position => $param) {
                $names[$position] = $param->getName();
            }

            $this->constructorParameterNamesCache[$className] = $names;

            return $this->constructorParameterNamesCache[$className];
        } catch (ReflectionException) {
            $this->constructorParameterNamesCache[$className] = [];

            return $this->constructorParameterNamesCache[$className];
        }
    }

    /**
     * @param string $paramName
     * @return bool
     */
    protected function isMessageParamName(string $paramName): bool
    {
        return strtolower(substr($paramName, -7)) === 'message';
    }

    /**
     * @param \PhpParser\Node\Identifier $name
     * @return bool
     */
    protected function isMessageArgName(Identifier $name): bool
    {
        return $this->isMessageParamName($name->name);
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
        if ($node instanceof Class_) {
            $this->resetDetection();
        }

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
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast)
    {
        return null;
    }
}
