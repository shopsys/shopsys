<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DomainType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domainConfigs'] = $this->getSortedDomainConfigsByAdminDomainTabs();
        $view->vars['displayUrl'] = $options['displayUrl'];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'displayUrl' => false,
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function getSortedDomainConfigsByAdminDomainTabs(): array
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $list = [];
        $list[] = $this->adminDomainTabsFacade->getSelectedDomainConfig();

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            if ($domainConfig->getId() !== $selectedDomainId) {
                $list[] = $domainConfig;
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return IntegerType::class;
    }
}
