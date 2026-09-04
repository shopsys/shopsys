<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SeoGroupType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['domain_id'] === null) {
            $seoAttributesOptionsByDomainId = [];

            foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
                $domainId = $domainConfig->getId();
                $seoAttributesOptionsByDomainId[$domainId] = [
                    'placeholder_source_input_id' => $this->resolvePlaceholderSourceInputId($options, $domainConfig),
                    'domain_id' => $domainId,
                ];
            }

            $builder->add('seo', MultidomainType::class, [
                'entry_type' => SeoAttributesFormType::class,
                'entry_options' => ['h1_required' => $options['h1_required']],
                'options_by_domain_id' => $seoAttributesOptionsByDomainId,
                'required' => false,
                'label' => false,
            ]);
        } else {
            $builder->add('seo', SeoAttributesFormType::class, [
                'placeholder_source_input_id' => $options['placeholder_source_input_id'],
                'h1_required' => $options['h1_required'],
                'domain_id' => $options['domain_id'],
            ]);
        }

        if ($options['url_list_options'] === null) {
            return;
        }

        $urlListOptions = $options['url_list_options'];

        if ($options['domain_id'] !== null && !array_key_exists('limit_domains_by_ids', $urlListOptions)) {
            $urlListOptions['limit_domains_by_ids'] = [$options['domain_id']];
        }

        $builder->add('urls', UrlListType::class, ['label' => 'URL addresses'] + $urlListOptions);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['multidomain'] = $options['domain_id'] === null;
    }

    #[Override]
    public function getParent(): string
    {
        return GroupType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('SEO'),
            'placeholder_source_input_id' => null,
            'h1_required' => false,
            'domain_id' => null,
            'url_list_options' => null,
        ]);
        $resolver->setAllowedTypes('placeholder_source_input_id', ['string', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setAllowedTypes('domain_id', ['int', 'null']);
        $resolver->setAllowedTypes('url_list_options', ['array', 'null']);
        $resolver->setInfo(
            'domain_id',
            'Null means the SEO attributes are edited for all domains at once, a domain id limits them to a single domain.',
        );
        $resolver->setInfo(
            'placeholder_source_input_id',
            'Id of the input holding the entity name — SEO title and H1 placeholders mirror it. The "{locale}" and "{domain_id}" tokens are replaced per domain.',
        );
        $resolver->setInfo(
            'url_list_options',
            'Options passed to UrlListType (route_name, entity_id, limit_domains_by_ids, required, constraints, …); null means the group has no "urls" field.',
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function resolvePlaceholderSourceInputId(array $options, DomainConfig $domainConfig): ?string
    {
        if ($options['placeholder_source_input_id'] === null) {
            return null;
        }

        return str_replace(
            ['{locale}', '{domain_id}'],
            [$domainConfig->getLocale(), (string)$domainConfig->getId()],
            $options['placeholder_source_input_id'],
        );
    }
}
