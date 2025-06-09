<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\ValidationNode;
use Override;
use Shopsys\FrameworkBundle\Form\Constraints\AntiXss as BaseAntiXss;
use Shopsys\FrameworkBundle\Form\Constraints\AntiXssValidator as BaseAntiXssValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use voku\helper\AntiXSS as VokuAntiXSS;

class MutationAntiXssValidator extends BaseAntiXssValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof BaseAntiXss) {
            throw new UnexpectedTypeException($constraint, BaseAntiXss::class);
        }

        if ($value instanceof ValidationNode) {
            $this->validateGraphQlNode($value, $constraint);

            return;
        }

        parent::validate($value, $constraint);
    }

    /**
     * @param \Overblog\GraphQLBundle\Validator\ValidationNode $validationNode
     * @param \Shopsys\FrameworkBundle\Form\Constraints\AntiXss $constraint
     */
    protected function validateGraphQlNode(ValidationNode $validationNode, BaseAntiXss $constraint): void
    {
        $args = $validationNode->getResolverArg('args');

        if (!$args instanceof Argument || $args->count() === 0) {
            return;
        }

        $antiXss = new VokuAntiXSS();

        foreach ($args->getArrayCopy() as $name => $argValue) {
            $this->validateRecursive($argValue, $name, $name, $constraint, $antiXss);
        }
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param array $excludedFields
     * @return bool
     */
    #[Override]
    protected function shouldExcludeField(string $fieldName, string $value, array $excludedFields): bool
    {
        if (parent::shouldExcludeField($fieldName, $value, $excludedFields)) {
            return true;
        }

        $excludedSuffixes = ['Id', 'Uuid', 'Token', 'Password', 'Hash', 'Url', 'Code'];

        foreach ($excludedSuffixes as $suffix) {
            if ($fieldName === strtolower($suffix) || str_ends_with($fieldName, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
