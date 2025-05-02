<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CollectAiFunctionsPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $list = [];

        foreach ($container->getDefinitions() as $definition) {
            // přeskoč abstraktní / synthetic / bez třídy
            if ($definition->isAbstract() || $definition->isSynthetic()) {
                continue;
            }
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            $rc = new ReflectionClass($class);

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                if ($m->getAttributes(AiFunction::class)) {
                    //                    $list[] = "$class::{$m->getName()}";
                    $list[] = serialize($this->getFunctionCallingSetup($rc, $m));
                    $definition->addTag('app.ai_dynamic_service');
                }
            }
        }

        $container->setParameter('app.ai_function_list', $list);
    }

    /**
     * @param \ReflectionClass $rc
     * @param \ReflectionMethod $m
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup
     */
    protected function getFunctionCallingSetup(ReflectionClass $rc, ReflectionMethod $m): FunctionRunnerSetup
    {
        $setup = new FunctionRunnerSetup();
        $setup->className = $rc->getName();
        $setup->functionName = $m->getName();
        $setup->returnType = $this->typeToString($m->getReturnType());

        foreach ($m->getParameters() as $p) {
            $paramSetup = new ParameterSetup();
            $paramSetup->parameterName = $p->getName();
            $paramSetup->parameterType = $this->typeToString($p->getType());
            $setup->params[] = $paramSetup;
        }

        [$attr] = $m->getAttributes(AiFunction::class);
        /** @var \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\AiFunction $options */
        $options = $attr->newInstance();
        $setup->aiFunctionName = $options->getAiFunctionName();

        return $setup;
    }

    /**
     * Vrátí string reprezentaci typu (int|Foo|null) nebo null
     *
     * @param \ReflectionType|null $type
     * @return string|null
     */
    protected function typeToString(?ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        // jednoduchý typ (string, Foo, atd.)
        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() ? '?' : '') . $type->getName();
        }

        // union typ (int|float)
        if ($type instanceof ReflectionUnionType) {
            $parts = [];

            foreach ($type->getTypes() as $t) {
                $parts[] = $t->getName();
            }

            return implode('|', $parts);
        }

        // intersection typy PHP 8.1+ (&) – zřídka, ale pro jistotu
        if ($type instanceof ReflectionIntersectionType) {
            $parts = [];

            /** @var \ReflectionNamedType $t */
            foreach ($type->getTypes() as $t) {
                $parts[] = $t->getName();
            }

            return implode('&', $parts);
        }

        return null;
    }
}
