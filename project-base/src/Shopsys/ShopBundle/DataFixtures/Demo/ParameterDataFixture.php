<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ParameterDataFixture extends AbstractReferenceFixture
{
    const REFERENCE_PREFIX = 'parameter_';
    const PRODUCT_PARAMETERS = [
        [
            'cs' => 'Barva',
            'en' => 'Color',
        ],
        [
            'cs' => 'Hmotnost',
            'en' => 'Weight',
        ],
    ];

    const PARAMETER_COLORS = [
        'black',
        'blue',
        'red',
        'green',
    ];

    const CZECH_LOCALE_PARAMETER_TRANSLATIONS_BY_ENGLISH_LOCALE = [
        'black' => 'černá',
        'blue' => 'modrá',
        'red' => 'červená',
        'green' => 'zelená',
    ];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface
     */
    private $parameterDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface $parameterDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        ParameterDataFactoryInterface $parameterDataFactory,
        ParameterFacade $parameterFacade
    ) {
        $this->parameterDataFactory = $parameterDataFactory;
        $this->parameterFacade = $parameterFacade;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::PRODUCT_PARAMETERS as $parameterNamesByLocale) {
            $visible = true;
            $parameterData = $this->parameterDataFactory->create();
            $parameterData->name = $parameterNamesByLocale;
            $parameterData->visible = $visible;
            $parameter = $this->parameterFacade->create($parameterData);
            $this->addReference(self::REFERENCE_PREFIX . strtolower($parameterNamesByLocale['en']), $parameter);
        }
    }
}
