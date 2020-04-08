<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Component\HttpFoundation\TransactionalMasterRequestListener;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsContainBadDomainUrlException;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainMainFriendlyUrlException;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ReadyCategorySeoMixFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixRepository
     */
    private $readyCategorySeoMixRepository;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->em = $em;
        $this->readyCategorySeoMixRepository = $readyCategorySeoMixRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function createOrEdit(ChoseCategorySeoMixCombination $choseCategorySeoMixCombination, ReadyCategorySeoMixData $readyCategorySeoMixData, UrlListData $urlListData): ReadyCategorySeoMix
    {
        $readyCategorySeoMix = $this->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);

        $this->em->beginTransaction();

        if ($readyCategorySeoMix === null) {
            $readyCategorySeoMix = new ReadyCategorySeoMix($readyCategorySeoMixData);
            $this->em->persist($readyCategorySeoMix);
            $this->em->flush();

            foreach ($readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues as $readyCategorySeoMixParameterParameterValue) {
                $readyCategorySeoMixParameterParameterValue->setReadyCategorySeoMix($readyCategorySeoMix);
                $this->em->persist($readyCategorySeoMixParameterParameterValue);
                $this->em->flush();
            }
        } else {
            $readyCategorySeoMix->edit($readyCategorySeoMixData);
            $this->em->flush();
        }

        $this->saveReadyCategoryMixFriendlyUrls($readyCategorySeoMix, $urlListData);

        try {
            $this->validateReadyCategoryMixFriendlyUrls($readyCategorySeoMix);
        } catch (ReadyCategorySeoMixUrlsContainBadDomainUrlException | ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException $e) {
            TransactionalMasterRequestListener::setTransactionHasBeenRollbacked();
            $this->em->rollback();
            throw $e;
        }

        $this->em->commit();

        return $readyCategorySeoMix;
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByChoseCategorySeoMixCombination(ChoseCategorySeoMixCombination $choseCategorySeoMixCombination): ?ReadyCategorySeoMix
    {
        return $this->readyCategorySeoMixRepository->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);
    }

    /**
     * @param int $id
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findById(int $id): ?ReadyCategorySeoMix
    {
        return $this->readyCategorySeoMixRepository->findById($id);
    }

    /**
     * @param int $id
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getById(int $id): ?ReadyCategorySeoMix
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixRepository->findById($id);

        if ($readyCategorySeoMix === null) {
            throw new ReadyCategorySeoMixNotFoundException(sprintf('ReadyCategorySeoMix with ID %s not found', $id));
        }

        return $readyCategorySeoMix;
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    public function delete(ReadyCategorySeoMix $readyCategorySeoMix): void
    {
        $this->em->remove($readyCategorySeoMix);
        $this->em->flush();
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     */
    private function saveReadyCategoryMixFriendlyUrls(ReadyCategorySeoMix $readyCategorySeoMix, UrlListData $urlListData): void
    {
        $this->friendlyUrlFacade->saveUrlListFormData('front_category_seo', $readyCategorySeoMix->getId(), $urlListData);

        $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($readyCategorySeoMix->getDomainId(), 'front_category_seo', $readyCategorySeoMix->getId());
        if ($mainFriendlyUrl === null) {
            $readyCategoryMixAllFriendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameAndEntityId('front_category_seo', $readyCategorySeoMix->getId());

            if (count($readyCategoryMixAllFriendlyUrls) === 0) {
                return;
            }

            $urlListDataForMainFriendlyUrl = new UrlListData();
            $urlListDataForMainFriendlyUrl->mainFriendlyUrlsByDomainId = [
                array_shift($readyCategoryMixAllFriendlyUrls),
            ];

            $this->friendlyUrlFacade->saveUrlListFormData('front_category_seo', $readyCategorySeoMix->getId(), $urlListDataForMainFriendlyUrl);
        }
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    private function validateReadyCategoryMixFriendlyUrls(ReadyCategorySeoMix $readyCategorySeoMix): void
    {
        $readyCategorySeoMixAllFriendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameAndEntityId('front_category_seo', $readyCategorySeoMix->getId());

        $hasCorrectDomainUrl = false;
        $hasMainFriendlyUrl = false;

        foreach ($readyCategorySeoMixAllFriendlyUrls as $index => $friendlyUrl) {
            if ($friendlyUrl->getDomainId() !== $readyCategorySeoMix->getDomainId()) {
                throw new ReadyCategorySeoMixUrlsContainBadDomainUrlException('ReadyCategorySeoMix urls contain bad domain url');
            }

            if ($friendlyUrl->isMain() === true && $hasMainFriendlyUrl === false) {
                $hasMainFriendlyUrl = true;
            }

            $hasCorrectDomainUrl = true;
        }

        if ($hasCorrectDomainUrl === false) {
            throw new ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException('ReadyCategorySeoMix urls do not contain url for correct domain');
        }

        if ($hasMainFriendlyUrl === false) {
            throw new ReadyCategorySeoMixUrlsDoNotContainMainFriendlyUrlException('ReadyCategorySeoMix urls do not contain main FriendlyUrl');
        }
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    public function deleteAllWithParameter(Parameter $parameter): void
    {
        $readyCategorySeoMixes = $this->readyCategorySeoMixRepository->getAllWithParameter($parameter);
        if (count($readyCategorySeoMixes) > 0) {
            foreach ($readyCategorySeoMixes as $readyCategorySeoMix) {
                $this->em->remove($readyCategorySeoMix);
            }
            $this->em->flush();
        }
    }
}
