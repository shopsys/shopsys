<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

use App\Component\Image\ImageFacade;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\SeoPage\Exception\DefaultSeoPageCannotBeDeletedException;
use App\Model\SeoPage\Exception\SeoPageNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class SeoPageFacade
{
    public const IMAGE_TYPE_OG = 'og';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\SeoPage\SeoPageRepository $seoPageRepository
     * @param \App\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly EntityManagerInterface $em,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly SeoPageRepository $seoPageRepository,
        private readonly ImageFacade $imageFacade,
    ) {
    }

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     * @return \App\Model\SeoPage\SeoPage
     */
    public function create(SeoPageData $seoPageData): SeoPage
    {
        $seoPage = new SeoPage($seoPageData);

        $this->em->persist($seoPage);
        $this->em->flush();

        foreach ($this->domain->getAll() as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_page_seo',
                $seoPage->getId(),
                $seoPageData->pageSlugsIndexedByDomainId[$domain->getId()],
                $domain->getId()
            );
        }

        $this->imageFacade->manageImages($seoPage, $seoPageData->seoOgImage, self::IMAGE_TYPE_OG);

        return $seoPage;
    }

    /**
     * @param int $seoPageId
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     * @return \App\Model\SeoPage\SeoPage
     */
    public function edit(int $seoPageId, SeoPageData $seoPageData): SeoPage
    {
        $seoPage = $this->seoPageRepository->getById($seoPageId);

        $seoPage->edit($seoPageData);

        $this->em->flush();

        $this->imageFacade->manageImages($seoPage, $seoPageData->seoOgImage, self::IMAGE_TYPE_OG);

        return $seoPage;
    }

    /**
     * @param int $seoPageId
     */
    public function delete(int $seoPageId): void
    {
        $seoPage = $this->seoPageRepository->getById($seoPageId);

        if ($seoPage->isDefaultPage()) {
            throw new DefaultSeoPageCannotBeDeletedException();
        }

        $this->em->remove($seoPage);
        $this->em->flush();

        $this->friendlyUrlFacade->removeFriendlyUrlsForAllDomains('front_page_seo', $seoPageId);
    }

    /**
     * @param int $seoPageId
     * @return \App\Model\SeoPage\SeoPage
     */
    public function getById(int $seoPageId): SeoPage
    {
        return $this->seoPageRepository->getById($seoPageId);
    }

    /**
     * @param int $domainId
     * @param string $pageSlug
     * @return \App\Model\SeoPage\SeoPage
     */
    public function getByDomainIdAndPageSlug(int $domainId, string $pageSlug): SeoPage
    {
        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($domainId, $pageSlug);

        if ($friendlyUrl === null || $friendlyUrl->getRouteName() !== 'front_page_seo') {
            $message = sprintf('SeoPage with slug \'%s\' not found.', $pageSlug);

            throw new SeoPageNotFoundException($message);
        }

        return $this->getById($friendlyUrl->getEntityId());
    }
}
