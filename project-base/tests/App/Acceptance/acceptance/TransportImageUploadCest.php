<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\App\Acceptance\acceptance\PageObject\Admin\EntityEditPage;
use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class TransportImageUploadCest
{
    private const IMAGE_UPLOAD_FIELD_ID = 'transport_form_image_image_file';
    private const SAVE_BUTTON_NAME = 'transport_form[save]';

    private const TEST_IMAGE_NAME = 'transportTestImage.png';

    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\EntityEditPage $entityEditPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     */
    public function testSuccessfulImageUpload(
        AcceptanceTester $me,
        EntityEditPage $entityEditPage,
        LoginPage $loginPage,
    ) {
        $me->wantTo('Upload an image in admin transport edit page');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/transport/edit/1');
        $entityEditPage->uploadTestImage(self::IMAGE_UPLOAD_FIELD_ID, self::TEST_IMAGE_NAME);
        $me->clickByName(self::SAVE_BUTTON_NAME);
        $me->seeTranslationAdmin(
            'Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> was modified',
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            [
                '{{ url }}' => '',
                '{{ name }}' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $me->getAdminLocale()),
            ],
        );
    }
}
