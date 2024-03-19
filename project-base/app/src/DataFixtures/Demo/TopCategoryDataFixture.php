<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
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
    public function load(ObjectManager $manager)
    {
        $categories = [
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class),
            $this->getReference(CategoryDataFixture::CATEGORY_TOYS, Category::class),
        ];

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->topCategoryFacade->saveTopCategoriesForDomain($domainId, $categories);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
        ];
    }
}
