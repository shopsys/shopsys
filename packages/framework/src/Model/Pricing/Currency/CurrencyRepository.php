<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManagerInterface;

class CurrencyRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getCurrencyRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Currency::class);
    }

    public function findById(int $currencyId): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->getCurrencyRepository()->find($currencyId);
    }

    public function findByCode(string $code): ?\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->getCurrencyRepository()->findOneBy([
            'code' => $code,
        ]);
    }

    public function getById(int $currencyId): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        $currency = $this->findById($currencyId);

        if ($currency === null) {
            $message = 'Currency with ID ' . $currencyId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException($message);
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
