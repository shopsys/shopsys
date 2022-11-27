<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleFacade
     */
    protected $moduleFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     */
    public function __construct(ModuleFacade $moduleFacade)
    {
        $this->moduleFacade = $moduleFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isModuleEnabled', [$this, 'isModuleEnabled']),
        ];
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnabled(string $moduleName): bool
    {
        return $this->moduleFacade->isEnabled($moduleName);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'module';
    }
}
