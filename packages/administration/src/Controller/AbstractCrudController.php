<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AbstractCrudController extends AbstractController
{
    private ?CrudConfigData $config = null;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    protected function configure(CrudConfig $config): CrudConfig
    {
        return $config;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('@ShopsysAdministration/crud/list.html.twig', [
            'title' => $this->getConfig()->getTitle(PageType::LIST),
        ]);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    final public function getConfig(): CrudConfigData
    {
        if ($this->config === null) {
            $this->config = $this->configure(new CrudConfig())->getConfig();
        }

        return $this->config;
    }
}
