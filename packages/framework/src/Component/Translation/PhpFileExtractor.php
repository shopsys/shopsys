<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Shopsys\FrameworkBundle\Component\Translation\Exception\ExtractionException;
use Shopsys\FrameworkBundle\Component\Translation\Exception\MessageIdArgumentNotPresent;
use SplFileInfo;
use Twig\Node\Node as TwigNode;

class PhpFileExtractor implements FileVisitorInterface, NodeVisitor
{
    protected NodeTraverser $traverser;

    protected MessageCatalogue $catalogue;

    protected SplFileInfo $file;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Translation\TransMethodSpecification[]
     */
    protected array $transMethodSpecifications;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\TransMethodSpecification[] $transMethodSpecifications
     */
    public function __construct(
        protected readonly PhpParserNodeHelper $phpParserNodeHelper,
        array $transMethodSpecifications,
    ) {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);

        $this->transMethodSpecifications = [];

        foreach ($transMethodSpecifications as $transMethodSpecification) {
            $methodName = $this->getNormalizedMethodName($transMethodSpecification->getMethodName());
            $this->transMethodSpecifications[$methodName] = $transMethodSpecification;
        }
    }

    #[Override]
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    #[Override]
    public function enterNode(Node $node): int|Node|null
    {
        if ($this->isTransMethodOrFuncCall($node)) {
            /** @var \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall $transNode */
            $transNode = $node;
            $messageId = $this->getMessageId($transNode);
            $domain = $this->getDomain($transNode);

            $message = new Message($messageId, $domain);
            $message->addSource(new FileSource((string)$this->file->getFilename(), $node->getLine()));

            $this->catalogue->add($message);
        }

        return null;
    }

    protected function getMessageId(MethodCall|FuncCall $node): string
    {
        $methodName = $this->getNormalizedMethodName($this->getNodeName($node));
        $messageIdArgumentIndex = $this->transMethodSpecifications[$methodName]->getMessageIdArgumentIndex();

        if (!isset($node->args[$messageIdArgumentIndex])) {
            throw new MessageIdArgumentNotPresent();
        }

        return $this->phpParserNodeHelper->getConcatenatedStringValue(
            $node->args[$messageIdArgumentIndex]->value,
            $this->file,
        );
    }

    protected function getDomain(MethodCall|FuncCall $node): string
    {
        $methodName = $this->getNormalizedMethodName($this->getNodeName($node));
        $domainArgumentIndex = $this->transMethodSpecifications[$methodName]->getDomainArgumentIndex();

        if ($domainArgumentIndex === null) {
            return Translator::DEFAULT_TRANSLATION_DOMAIN;
        }

        $domainArg = null;

        if (isset($node->args[$domainArgumentIndex]) && $node->args[$domainArgumentIndex] instanceof Node\Arg && $node->args[$domainArgumentIndex]->name === null) {
            $domainArg = $node->args[$domainArgumentIndex];
        } else {
            foreach ($node->args as $arg) {
                if (!$arg instanceof Node\Arg) {
                    continue;
                }

                if ($arg->name !== null && $arg->name->name === 'domain') {
                    $domainArg = $arg;

                    break;
                }
            }
        }

        if ($domainArg === null) {
            return Translator::DEFAULT_TRANSLATION_DOMAIN;
        }

        if ($domainArg->value instanceof Node\Expr\ConstFetch && (string)$domainArg->value->name === 'null') {
            return Translator::DEFAULT_TRANSLATION_DOMAIN;
        }

        return $this->phpParserNodeHelper->getConcatenatedStringValue(
            $domainArg->value,
            $this->file,
        );
    }

    protected function isTransMethodOrFuncCall(Node $node): bool
    {
        if ($node instanceof MethodCall || $node instanceof FuncCall) {
            try {
                $methodName = $this->getNormalizedMethodName($this->getNodeName($node));
            } catch (ExtractionException $ex) {
                return false;
            }

            if (array_key_exists($methodName, $this->transMethodSpecifications)) {
                return true;
            }
        }

        return false;
    }

    protected function getNormalizedMethodName(string $methodName): string
    {
        return mb_strtolower($methodName);
    }

    protected function getNodeName(Node $node): string
    {
        if ($node instanceof MethodCall) {
            return (string)$node->name;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            return (string)$node->name;
        }

        throw new ExtractionException('Unable to resolve node name');
    }

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    #[Override]
    public function leaveNode(Node $node): int|Node|null
    {
        return null;
    }

    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    #[Override]
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): null
    {
        return null;
    }

    #[Override]
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast): null
    {
        return null;
    }
}
