<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     */
    public function __construct(protected readonly ModuleFacade $moduleFacade)
    {
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isModuleEnabled', [$this, 'isModuleEnabled']),
        ];
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->moduleFacade->isEnabled($moduleName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'module';
    }
}
