<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ParameterFilter;

use App\DataFixtures\Demo\ParameterDataFixture;
use App\FrontendApi\Model\Component\Constraints\ParameterFilter;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ParameterFilterValidationTest extends GraphQlTestCase
{
    public function testValuesNotSupportedForSliderType(): void
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameterSlider */
        $parameterSlider = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);

        $mutation = 'query {
  category(urlSlug: "/personal-computers-accessories") {
    products(filter: {
      parameters: [{
        parameter: "' . $parameterSlider->getUuid() . '"
        values: ["' . Uuid::uuid4()->toString() . '"]
      }]
    }) {
      edges {
        node {name}
      }
    }
  }
}';
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(ParameterFilter::VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR, $validationErrors['filter.parameters[0]'][0]['code']);
    }

    public function testMinMaxNotSupportedForNonSliderType(): void
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameterNonSlider */
        $parameterNonSlider = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()));

        $mutation = 'query {
  category(urlSlug: "/personal-computers-accessories") {
    products(filter: {
      parameters: [{
        parameter: "' . $parameterNonSlider->getUuid() . '"
        minimalValue: 10
        maximalValue: 20
      }]
    }) {
      edges {
        node {name}
      }
    }
  }
}';
        $response = $this->getResponseContentForQuery($mutation);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(ParameterFilter::MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR, $validationErrors['filter.parameters[0]'][0]['code']);
    }
}
