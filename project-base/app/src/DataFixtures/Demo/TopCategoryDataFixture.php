<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;

class TopCategoryDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade $topCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private readonly TopCategoryFacade $topCategoryFacade, private readonly Domain $domain)
    {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $categories = [
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->getReference(CategoryDataFixture::CATEGORY_TOYS),
        ];

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->topCategoryFacade->saveTopCategoriesForDomain($domainId, $categories);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
        ];
    }
}
