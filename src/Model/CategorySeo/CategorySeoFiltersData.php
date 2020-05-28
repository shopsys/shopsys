<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CategorySeoFiltersData
{
    /**
     * @var bool|null
     */
    public $useFlags;

    /**
     * @var bool|null
     */
    public $useOrdering;

    /**
     * @var \App\Model\Product\Parameter\Parameter[]
     */
    public $parameters = [];

    /**
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     * @param mixed $payload
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if ($this->useFlags === false && $this->useOrdering === false) {
            $context->buildViolation(t('Prosím vyberte alespoň jedno z příznaků nebo řazení.'))
                ->atPath('useFlags')
                ->addViolation();
        }
    }
}
