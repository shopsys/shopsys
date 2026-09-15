<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The "SEO" card of an entity form — one DomainSeoType per domain enabled in the administration,
 * or a single one when the entity belongs to one domain
 */
final class SeoGroupType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainSeoOptions = [
            'placeholder_source_path' => $options['placeholder_source_path'],
            'h1_required' => $options['h1_required'],
            'url_list_options' => $options['url_list_options'],
        ];

        if ($options['domain_id'] !== null) {
            $builder->add('domain', DomainSeoType::class, ['domain_id' => $options['domain_id']] + $domainSeoOptions);

            return;
        }

        $optionsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $optionsByDomainId[$domainId] = ['domain_id' => $domainId];
        }

        $builder->add('domains', MultidomainType::class, [
            'inherit_data' => true,
            'entry_type' => DomainSeoType::class,
            'entry_options' => ['multidomain' => true] + $domainSeoOptions,
            'options_by_domain_id' => $optionsByDomainId,
        ]);
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
            'domain_id' => null,
            'placeholder_source_path' => null,
            'h1_required' => false,
            'url_list_options' => null,
        ]);
        $resolver->setAllowedTypes('domain_id', ['int', 'null']);
        $resolver->setAllowedTypes('placeholder_source_path', ['string[]', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setAllowedTypes('url_list_options', ['array', 'null']);
        $resolver->setInfo(
            'domain_id',
            'Null means the SEO attributes are edited for all domains enabled in the administration, a domain id limits them to the single domain the entity belongs to.',
        );
        $resolver->setInfo(
            'placeholder_source_path',
            'Names of the nested fields leading from the form root to the input holding the entity name, e.g. ["basicInformation", "name"] — SEO title and H1 placeholders mirror it. The "{locale}" and "{domain_id}" tokens are replaced per domain.',
        );
        $resolver->setInfo(
            'url_list_options',
            'Options passed to UrlListType (route_name, entity_id, required, constraints, …); null means the group has no URL addresses.',
        );
    }
}
