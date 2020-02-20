<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Constraints;

use App\Component\Placeholder\PlaceholderConverter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UrlOrPlaceholderValidator extends ConstraintValidator
{
    /**
     * @var \App\Component\Placeholder\PlaceholderConverter
     */
    private $placeholderConverter;

    /**
     * @param \App\Component\Placeholder\PlaceholderConverter $placeholderConverter
     */
    public function __construct(PlaceholderConverter $placeholderConverter)
    {
        $this->placeholderConverter = $placeholderConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UrlOrPlaceholder) {
            throw new UnexpectedTypeException($constraint, UrlOrPlaceholder::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string)$value;
        if ('' === $value) {
            return;
        }

        $placeholderName = $this->placeholderConverter->resolvePlaceholderNameFromText($value);

        if ($placeholderName !== null) {
            if (!in_array($placeholderName, $constraint->allowedPlaceholders, true)) {
                $this->context->buildViolation($constraint->notAllowedPlaceholderMessage)
                    ->setParameter('{{ placeholderName }}', $this->formatValue($placeholderName))
                    ->setCode(UrlOrPlaceholder::INVALID_URL_OR_PLACEHOLDER_ERROR)
                    ->addViolation();
            }

            return;
        }

        $this->validateUrl($value, $constraint);
    }

    /**
     * @param $value
     * @param \App\Component\Placeholder\Constraints\UrlOrPlaceholder $constraint
     */
    private function validateUrl($value, UrlOrPlaceholder $constraint): void
    {
        $pattern = sprintf(UrlValidator::PATTERN, implode('|', $constraint->protocols));

        if (!preg_match($pattern, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UrlOrPlaceholder::INVALID_URL_OR_PLACEHOLDER_ERROR)
                ->addViolation();

            return;
        }

        if ($constraint->checkDns) {
            // backwards compatibility
            if (true === $constraint->checkDns) {
                $constraint->checkDns = UrlOrPlaceholder::CHECK_DNS_TYPE_ANY;
                // @codingStandardsIgnoreLine
                @trigger_error(sprintf('Use of the boolean TRUE for the "checkDns" option in %s is deprecated.  Use UrlOrPlaceholder::CHECK_DNS_TYPE_ANY instead.', UrlOrPlaceholder::class), E_USER_DEPRECATED);
            }

            if (!in_array($constraint->checkDns, [
                UrlOrPlaceholder::CHECK_DNS_TYPE_ANY,
                UrlOrPlaceholder::CHECK_DNS_TYPE_A,
                UrlOrPlaceholder::CHECK_DNS_TYPE_A6,
                UrlOrPlaceholder::CHECK_DNS_TYPE_AAAA,
                UrlOrPlaceholder::CHECK_DNS_TYPE_CNAME,
                UrlOrPlaceholder::CHECK_DNS_TYPE_MX,
                UrlOrPlaceholder::CHECK_DNS_TYPE_NAPTR,
                UrlOrPlaceholder::CHECK_DNS_TYPE_NS,
                UrlOrPlaceholder::CHECK_DNS_TYPE_PTR,
                UrlOrPlaceholder::CHECK_DNS_TYPE_SOA,
                UrlOrPlaceholder::CHECK_DNS_TYPE_SRV,
                UrlOrPlaceholder::CHECK_DNS_TYPE_TXT,
            ], true)) {
                throw new InvalidOptionsException(sprintf('Invalid value for option "checkDns" in constraint %s', get_class($constraint)), ['checkDns']);
            }

            $host = parse_url($value, PHP_URL_HOST);

            if (!is_string($host) || !checkdnsrr($host, $constraint->checkDns)) {
                $this->context->buildViolation($constraint->dnsMessage)
                    ->setParameter('{{ value }}', $this->formatValue($host))
                    ->setCode(UrlOrPlaceholder::INVALID_URL_OR_PLACEHOLDER_ERROR)
                    ->addViolation();
            }
        }
    }
}
