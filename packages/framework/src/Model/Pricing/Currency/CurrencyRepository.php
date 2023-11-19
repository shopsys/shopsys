<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException;

class CurrencyRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCurrencyRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Currency::class);
    }

    /**
     * @param int $currencyId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public function findById($currencyId): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->getCurrencyRepository()->find($currencyId);
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public function findByCode($code): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->getCurrencyRepository()->findOneBy([
            'code' => $code,
        ]);
    }

    /**
     * @param string $currencyCode
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getByCode(string $currencyCode): Currency
    {
        $currency = $this->findByCode($currencyCode);

        if ($currency === null) {
            $message = 'Currency with code "' . $currencyCode . '" not found.';

            throw new CurrencyNotFoundException($message);
        }

        return $currency;
    }

    /**
     * @param int $currencyId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getById($currencyId): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        $currency = $this->findById($currencyId);

        if ($currency === null) {
            $message = 'Currency with ID ' . $currencyId . ' not found.';

            throw new CurrencyNotFoundException($message);
        }

        return $currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getAll(): array
    {
        return $this->getCurrencyRepository()->findAll();
    }
}
