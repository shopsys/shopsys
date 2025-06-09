<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use voku\helper\AntiXSS as VokuAntiXSS;

class AntiXssValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AntiXss) {
            throw new UnexpectedTypeException($constraint, AntiXss::class);
        }

        $antiXss = new VokuAntiXSS();

        $this->validateRecursive($value, '', '', $constraint, $antiXss);
    }

    /**
     * @param mixed $value
     * @param string $fieldName
     * @param string $path
     * @param \Shopsys\FrameworkBundle\Form\Constraints\AntiXss $constraint
     * @param \voku\helper\AntiXSS $antiXss
     */
    protected function validateRecursive(
        mixed $value,
        string $fieldName,
        string $path,
        AntiXss $constraint,
        VokuAntiXSS $antiXss,
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        if (is_string($value)) {
            if (!$this->shouldExcludeField($fieldName, $value, $constraint->excludedFields)) {
                $antiXss->xss_clean($value);

                if ($antiXss->isXssFound()) {
                    $this->context->buildViolation($constraint->message)->atPath($path)->setCode($constraint::ERROR_CODE)->addViolation();
                }
            }
        } elseif (is_array($value)) {
            foreach ($value as $key => $item) {
                $newPath = $path === '' ? $key : $path . ".{$key}";
                $this->validateRecursive($item, (string)$key, $newPath, $constraint, $antiXss);
            }
        } elseif (is_object($value)) {
            foreach (get_object_vars($value) as $property => $propertyValue) {
                $newPath = $path === '' ? $property : $path . ".{$property}.";
                $this->validateRecursive($propertyValue, $property, $newPath, $constraint, $antiXss);
            }
        }
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param string[] $excludedFields
     * @return bool
     */
    protected function shouldExcludeField(string $fieldName, string $value, array $excludedFields): bool
    {
        return in_array($fieldName, $excludedFields, true);
    }
}
