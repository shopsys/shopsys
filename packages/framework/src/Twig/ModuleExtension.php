<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Twig_Extension;
use Twig_SimpleFunction;

class ModuleExtension extends Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleFacade
     */
    private $moduleFacade;

    public function __construct(ModuleFacade $moduleFacade)
    {
        $this->moduleFacade = $moduleFacade;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isModuleEnabled', [$this, 'isModuleEnabled']),
        ];
    }
    
    public function isModuleEnabled(int $moduleName): string
    {
        return $this->moduleFacade->isEnabled($moduleName);
    }

    public function getName(): string
    {
        return 'module';
    }
}
