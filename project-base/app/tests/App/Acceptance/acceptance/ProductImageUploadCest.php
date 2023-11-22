<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\App\Acceptance\acceptance\PageObject\Admin\EntityEditPage;
use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class ProductImageUploadCest
{
    private const IMAGE_UPLOAD_FIELD_ID = 'product_form_imageGroup_images_file';
    private const SAVE_BUTTON_NAME = 'product_form[save]';

    private const TEST_IMAGE_NAME = 'productTestImage.png';

    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\EntityEditPage $entityEditPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     */
    public function testSuccessfulImageUpload(
        AcceptanceTester $me,
        EntityEditPage $entityEditPage,
        LoginPage $loginPage,
    ): void {
        $me->wantTo('upload image in admin product edit page');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/product/edit/1');
        $entityEditPage->uploadTestImage(self::IMAGE_UPLOAD_FIELD_ID, self::TEST_IMAGE_NAME);
        $me->clickByName(self::SAVE_BUTTON_NAME);
        $me->seeTranslationAdmin(
            'Product <strong><a href="{{ url }}">{{ product|productDisplayName }}</a></strong> modified',
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            [
                '{{ url }}' => '',
                '{{ product|productDisplayName }}' => t(
                    '22" Sencor SLE 22F46DM4 HELLO KITTY',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $me->getAdminLocale(),
                ),
            ],
        );
    }
}
