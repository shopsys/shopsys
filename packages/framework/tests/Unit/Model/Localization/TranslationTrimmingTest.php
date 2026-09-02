<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Localization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Image\ImageTranslation;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleTranslation;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorTranslation;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryTranslation;
use Shopsys\FrameworkBundle\Model\Category\CategoryTranslation;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation;
use Shopsys\FrameworkBundle\Model\Country\CountryTranslation;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation;
use Shopsys\FrameworkBundle\Model\Product\ProductTranslation;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslations;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupTranslation;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation;

class TranslationTrimmingTest extends TestCase
{
    /**
     * @return iterable<string, array{translationClass: class-string, propertyName: string}>
     */
    public static function trimmedTranslationPropertyProvider(): iterable
    {
        $propertiesByTranslationClass = [
            ProductTranslation::class => ['name', 'variantAlias', 'namePrefix', 'nameSuffix'],
            CategoryTranslation::class => ['name'],
            PaymentTranslation::class => ['name', 'description', 'instructions'],
            TransportTranslation::class => ['name', 'description', 'instructions', 'trackingInstruction'],
            TransportGroupTranslation::class => ['name'],
            ParameterTranslation::class => ['name'],
            ParameterGroupTranslation::class => ['name'],
            BrandTranslation::class => ['description'],
            BlogArticleAuthorTranslation::class => ['jobTitle', 'description'],
            ProductVideoTranslations::class => ['description'],
            UnitTranslation::class => ['name'],
            FlagTranslation::class => ['name'],
            OrderStatusTranslation::class => ['name'],
            BlogArticleTranslation::class => ['name', 'description', 'perex'],
            BlogCategoryTranslation::class => ['name', 'description'],
            ImageTranslation::class => ['name'],
            ComplaintStatusTranslation::class => ['name'],
            CustomerUserRoleGroupTranslation::class => ['name'],
            CountryTranslation::class => ['name'],
            UploadedFileTranslation::class => ['name'],
        ];

        foreach ($propertiesByTranslationClass as $translationClass => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                yield $translationClass . '::$' . $propertyName => [
                    'translationClass' => $translationClass,
                    'propertyName' => $propertyName,
                ];
            }
        }
    }

    /**
     * @param class-string $translationClass
     */
    #[DataProvider('trimmedTranslationPropertyProvider')]
    public function testValueIsTrimmedAndEmptyValueBecomesNull(string $translationClass, string $propertyName): void
    {
        $translation = new $translationClass();
        $setter = 'set' . ucfirst($propertyName);
        $getter = 'get' . ucfirst($propertyName);

        $translation->{$setter}("\t  value with spaces  \n");
        $this->assertSame('value with spaces', $translation->{$getter}());

        $translation->{$setter}('');
        $this->assertNull($translation->{$getter}());

        $translation->{$setter}('   ');
        $this->assertNull($translation->{$getter}());

        $translation->{$setter}(null);
        $this->assertNull($translation->{$getter}());
    }
}
