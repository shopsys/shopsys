<?php

declare(strict_types=1);

namespace App\Form;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DomainType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DomainTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(Domain $domain, AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        $this->domain = $domain;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['domainConfigs'] = $this->getSortedDomainConfigsByAdminDomainTabs();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function getSortedDomainConfigsByAdminDomainTabs(): array
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $list = [];
        $list[] = $this->adminDomainTabsFacade->getSelectedDomainConfig();

        foreach ($this->domain->getAll() as $domainConfig) {
            if ($domainConfig->getId() !== $selectedDomainId) {
                $list[] = $domainConfig;
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield DomainType::class;
    }
}
