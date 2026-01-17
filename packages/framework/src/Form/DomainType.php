<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DomainType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domainConfigs'] = $this->getSortedDomainConfigsByAdminDomainTabs($options['limit_domains_by_ids']);
        $view->vars['displayUrl'] = $options['displayUrl'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'displayUrl' => false,
                'limit_domains_by_ids' => [],
            ])
            ->setAllowedTypes('limit_domains_by_ids', 'array');
    }

    /**
     * @param int[] $limitDomainsByIds
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function getSortedDomainConfigsByAdminDomainTabs(array $limitDomainsByIds): array
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $list = [];
        $list[] = $this->adminDomainTabsFacade->getSelectedDomainConfig();

        foreach ($this->domain->getAdminEnabledDomains($limitDomainsByIds) as $domainConfig) {
            if ($domainConfig->getId() !== $selectedDomainId) {
                $list[] = $domainConfig;
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return IntegerType::class;
    }
}
