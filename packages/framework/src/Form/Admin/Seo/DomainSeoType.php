<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SEO attributes and URL addresses of an entity on a single domain. Inherits the entity data and maps its fields
 * to "seo"/"urls" or to "seo[domainId]"/"urls[domainId]" depending on whether the entity is multidomain.
 */
final class DomainSeoType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('seo', SeoAttributesType::class, [
            'property_path' => $this->resolvePropertyPath('seo', $options),
            'domain_id' => $options['domain_id'],
            'placeholder_source_path' => $options['placeholder_source_path'],
            'h1_required' => $options['h1_required'],
        ]);

        if ($options['url_list_options'] === null) {
            return;
        }

        $builder->add('urls', UrlListType::class, [
            'property_path' => $this->resolvePropertyPath('urls', $options),
            'label' => 'URL addresses',
            'domain_id' => $options['domain_id'],
        ] + $options['url_list_options']);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('domain_id');
        $resolver->setDefaults([
            'inherit_data' => true,
            'label' => false,
            'multidomain' => false,
            'placeholder_source_path' => null,
            'h1_required' => false,
            'url_list_options' => null,
        ]);
        $resolver->setAllowedTypes('domain_id', 'int');
        $resolver->setAllowedTypes('multidomain', 'bool');
        $resolver->setAllowedTypes('placeholder_source_path', ['string[]', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setAllowedTypes('url_list_options', ['array', 'null']);
        $resolver->setInfo(
            'multidomain',
            'True when the entity data hold the SEO attributes and URL addresses in arrays indexed by the domain id ("seo[1]"), false when the entity belongs to a single domain and holds plain objects ("seo").',
        );
        $resolver->setInfo(
            'url_list_options',
            'Options passed to UrlListType (route_name, entity_id, required, constraints, …); null means the domain has no "urls" field.',
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function resolvePropertyPath(string $propertyName, array $options): string
    {
        if ($options['multidomain']) {
            return sprintf('%s[%d]', $propertyName, $options['domain_id']);
        }

        return $propertyName;
    }
}
