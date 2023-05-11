<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
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

    protected DocParser $docParser;

    protected MessageCatalogue $catalogue;

    protected SplFileInfo $file;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Translation\TransMethodSpecification[]
     */
    protected array $transMethodSpecifications;

    protected ?Node $previousNode = null;

    /**
     * @param \Doctrine\Common\Annotations\DocParser $docParser
     * @param \Shopsys\FrameworkBundle\Component\Translation\TransMethodSpecification[] $transMethodSpecifications
     */
    public function __construct(DocParser $docParser, array $transMethodSpecifications)
    {
        $this->docParser = $docParser;
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);

        $this->transMethodSpecifications = [];

        foreach ($transMethodSpecifications as $transMethodSpecification) {
            $methodName = $this->getNormalizedMethodName($transMethodSpecification->getMethodName());
            $this->transMethodSpecifications[$methodName] = $transMethodSpecification;
        }
    }

    /**
     * @param \SplFileInfo $file
     * @param \JMS\TranslationBundle\Model\MessageCatalogue $catalogue
     * @param array $ast
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
        if ($this->isTransMethodOrFuncCall($node)) {
            if (!$this->isIgnored($node)) {
                /** @var \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall $transNode */
                $transNode = $node;
                $messageId = $this->getMessageId($transNode);
                $domain = $this->getDomain($transNode);

                $message = new Message($messageId, $domain);
                $message->addSource(new FileSource((string)$this->file->getFilename(), $node->getLine()));

                $this->catalogue->add($message);
            }
        }

        $this->previousNode = $node;

        return null;
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $node
     * @return string
     */
    protected function getMessageId($node)
    {
        $methodName = $this->getNormalizedMethodName($this->getNodeName($node));
        $messageIdArgumentIndex = $this->transMethodSpecifications[$methodName]->getMessageIdArgumentIndex();

        if (!isset($node->args[$messageIdArgumentIndex])) {
            throw new MessageIdArgumentNotPresent();
        }

        return PhpParserNodeHelper::getConcatenatedStringValue(
            $node->args[$messageIdArgumentIndex]->value,
            $this->file
        );
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $node
     * @return string
     */
    protected function getDomain($node)
    {
        $methodName = $this->getNormalizedMethodName($this->getNodeName($node));
        $domainArgumentIndex = $this->transMethodSpecifications[$methodName]->getDomainArgumentIndex();

        if ($domainArgumentIndex !== null && isset($node->args[$domainArgumentIndex])) {
            return PhpParserNodeHelper::getConcatenatedStringValue(
                $node->args[$domainArgumentIndex]->value,
                $this->file
            );
        }

        return Translator::DEFAULT_TRANSLATION_DOMAIN;
    }

    /**
     * @param \PhpParser\Node $node
     * @return bool
     */
    protected function isTransMethodOrFuncCall(Node $node)
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

    /**
     * @param \PhpParser\Node $node
     * @return bool
     */
    protected function isIgnored(Node $node)
    {
        foreach ($this->getAnnotations($node) as $annotation) {
            if ($annotation instanceof Ignore) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \PhpParser\Node $node
     * @return \Doctrine\Common\Annotations\Annotation[]|\JMS\TranslationBundle\Annotation\Ignore[]
     */
    protected function getAnnotations(Node $node)
    {
        $docComment = $this->getDocComment($node);

        if ($docComment !== null) {
            return $this->docParser->parse(
                $docComment->getText(),
                'file ' . $this->file . ' near line ' . $node->getLine()
            );
        }

        return [];
    }

    /**
     * @param \PhpParser\Node $node
     * @return \PhpParser\Comment\Doc|null
     */
    protected function getDocComment(Node $node)
    {
        $docComment = $node->getDocComment();

        if ($docComment === null) {
            if ($this->previousNode !== null) {
                $docComment = $this->previousNode->getDocComment();
            }
        }

        return $docComment;
    }

    /**
     * @param string $methodName
     * @return string
     */
    protected function getNormalizedMethodName($methodName)
    {
        return mb_strtolower($methodName);
    }

    /**
     * @param \PhpParser\Node $node
     * @return string
     */
    protected function getNodeName(Node $node)
    {
        if ($node instanceof MethodCall) {
            return (string)$node->name;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            return (string)$node->name;
        }

        throw new ExtractionException('Unable to resolve node name');
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
    public function leaveNode(Node $node): int|Node|null
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
     * @param \SplFileInfo $file
     * @param \JMS\TranslationBundle\Model\MessageCatalogue $catalogue
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
