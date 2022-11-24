<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException;

class CurrencyRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
    public function findById(int $currencyId): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->getCurrencyRepository()->find($currencyId);
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public function findByCode(string $code): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
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
    public function getById(int $currencyId): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        $currency = $this->findById($currencyId);

        if ($currency === null) {
            $message = 'Currency with ID ' . $currencyId . ' not found.';
            throw new CurrencyNotFoundException($message);
        }

        return $currency;
    }

    /**
     * @return object[]
     */
    public function getAll(): array
    {
        return $this->getCurrencyRepository()->findAll();
    }
}
