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
        if ($options['multidomain']) {
            $seoAttributesOptionsByDomainId = [];

            foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
                $seoAttributesOptionsByDomainId[$domainConfig->getId()] = [
                    'placeholder_source_input_id' => $this->resolvePlaceholderSourceInputId($options, $domainConfig),
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
            ]);
        }

        if ($options['url_list_options'] !== null) {
            $builder->add('urls', UrlListType::class, ['label' => 'URL addresses'] + $options['url_list_options']);
        }
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['multidomain'] = $options['multidomain'];
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
            'multidomain' => true,
            'placeholder_source_input_id' => null,
            'h1_required' => false,
            'url_list_options' => null,
        ]);
        $resolver->setAllowedTypes('multidomain', 'bool');
        $resolver->setAllowedTypes('placeholder_source_input_id', ['string', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setAllowedTypes('url_list_options', ['array', 'null']);
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
