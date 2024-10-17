<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class DeleteReadyCategorySeoMixFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     */
    public function deleteAllWithParameter(Parameter $parameter): void
    {
        $readyCategorySeoMixes = $this->readyCategorySeoMixRepository->getAllWithParameter($parameter);

        if (count($readyCategorySeoMixes) === 0) {
            return;
        }

        foreach ($readyCategorySeoMixes as $readyCategorySeoMix) {
            $this->em->remove($readyCategorySeoMix);
        }
        $this->em->flush();
    }
}
