<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException;

class CurrencyRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getCurrencyRepository(): EntityRepository
    {
        return $this->em->getRepository(Currency::class);
    }

    public function findById(int $currencyId): ?Currency
    {
        return $this->getCurrencyRepository()->find($currencyId);
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->getCurrencyRepository()->findOneBy([
            'code' => $code,
        ]);
    }

    public function getByCode(string $currencyCode): Currency
    {
        $currency = $this->findByCode($currencyCode);

        if ($currency === null) {
            $message = 'Currency with code "' . $currencyCode . '" not found.';

            throw new CurrencyNotFoundException($message);
        }

        return $currency;
    }

    public function getById(int $currencyId): Currency
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
