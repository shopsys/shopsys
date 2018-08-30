<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use SplFileInfo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Twig_Node;

class ConstraintViolationExtractor implements FileVisitorInterface, NodeVisitor
{
    private const CONSTRAINT_MESSAGE_METHOD_NAME = 'addViolation';

    /**
     * @var \PhpParser\NodeTraverser
     */
    private $traverser;

    /**
     * @var \JMS\TranslationBundle\Model\MessageCatalogue
     */
    private $catalogue;

    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $interfaceInstanceName;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this);
        $this->interfaceInstanceName = '';
    }

    /**
     * @inheritdoc
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod && $this->haveMethodContextInterfaceImplemented($node) === true) {
            foreach ($node->stmts as $stmt) {
                $this->recursiveFindMessages($stmt);
            }
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @return bool
     */
    private function haveMethodContextInterfaceImplemented(ClassMethod $node)
    {
        foreach ($node->getParams() as $param) {
            if ($this->haveParamExecutionContextInterface($param) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \PhpParser\Node\Param $param
     * @return bool
     */
    private function haveParamExecutionContextInterface(Node\Param $param)
    {
        if ($param->type instanceof FullyQualified) {
            $className = implode('\\', $param->type->parts);
            $interfaceName = ExecutionContextInterface::class;
            $this->interfaceInstanceName = $param->name;

            return $className === $interfaceName;
        }

        return false;
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function recursiveFindMessages(Node $node)
    {
        if ($node instanceof MethodCall &&
            $node->var instanceof Variable &&
            $node->var->name === $this->interfaceInstanceName &&
            $node->name === self::CONSTRAINT_MESSAGE_METHOD_NAME
        ) {
            $this->extractMessage($node);
        }

        $variables = get_object_vars($node);
        foreach ($variables as $variable) {
            if (is_object($variable)) {
                $this->recursiveFindMessages($variable);
            } elseif (is_array($variable)) {
                foreach ($variable as $value) {
                    if (is_object($value)) {
                        $this->recursiveFindMessages($value);
                    }
                }
            }
        }
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     */
    private function extractMessage(MethodCall $methodCall)
    {
        $firstArgumentWithMessage = reset($methodCall->args);
        if ($firstArgumentWithMessage->value instanceof String_) {
            $messageId = $firstArgumentWithMessage->value->value; // value with translatable message

            $message = new Message($messageId, ConstraintMessageExtractor::CONSTRAINT_MESSAGE_DOMAIN);
            $message->addSource(new FileSource($this->file->getFilename(), $firstArgumentWithMessage->getLine()));

            $this->catalogue->add($message);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeTraverse(array $nodes)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterTraverse(array $nodes)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, Twig_Node $ast)
    {
        return null;
    }
}
