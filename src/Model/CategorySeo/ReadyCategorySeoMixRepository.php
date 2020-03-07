<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReadyCategorySeoMixRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(ReadyCategorySeoMix::class);
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByChoseCategorySeoMixCombination(ChoseCategorySeoMixCombination $choseCategorySeoMixCombination): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->findOneBy([
            'choseCategorySeoMixCombinationJson' => $choseCategorySeoMixCombination->getInJson(),
        ]);
    }

    /**
     * @param int $id
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findById(int $id): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->find($id);
    }
}
