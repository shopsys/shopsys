<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ReadyCategorySeoDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory
     */
    private $readyCategorySeoMixDataFactory;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->readyCategorySeoMixDataFactory = $readyCategorySeoMixDataFactory;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $choseCategorySeoMixCombinationArray = [
            'domainId' => 1,
            'categoryId' => 8,
            'flagId' => 1,
            'ordering' => null,
            'parameterValueIdsByParameterIds' => [
                38 => 75,
                40 => 79,
                37 => 73,
                39 => 77,
            ],
        ];

        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            '2 litrové kávovary - novinky',
            ['2-litrove-kavovary-novinky', 'nove-2-litrove-kavovary-vedlejsi-adresa-ktera-by-mela-byt-presmerovana-na-hlavni'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 2;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Nejprodávanější 2 litrové kávovary',
            ['nejprodavanejsi-2-litrove-kavovary'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 3;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            '2 litrové kávovary v akci',
            ['2-litrove-kavovary-v-akci'],
            1
        );
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param string $h1
     * @param string[] $slugs
     * @param int $domainId
     */
    private function createReadyCategorySeoMix(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
        string $h1,
        array $slugs,
        int $domainId
    ): void {
        $readyCategorySeoMixDataForForm = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixDataForForm(
            $choseCategorySeoMixCombination
        );
        $readyCategorySeoMixDataForForm->h1 = $h1;

        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createFromReadyCategorySeoMixDataForFormAndChoseCategorySeoMixCombination(
            $readyCategorySeoMixDataForForm,
            $choseCategorySeoMixCombination
        );

        $urlListData = new UrlListData();
        $urlListData->newUrls = [];
        foreach ($slugs as $slug) {
            $urlListData->newUrls[] = [
                'domain' => $domainId,
                'slug' => $slug,
            ];
        }

        $this->readyCategorySeoMixFacade->createOrEdit(
            $choseCategorySeoMixCombination,
            $readyCategorySeoMixData,
            $urlListData
        );
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
            FlagDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
