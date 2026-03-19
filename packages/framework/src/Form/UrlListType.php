<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains;
use Shopsys\FrameworkBundle\Form\Exception\MissingRouteNameException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GroupSequence;

final class UrlListType extends AbstractType
{
    private const string UNIQUE_SLUGS_VALIDATION_GROUP = 'UniqueSlugs';

    public function __construct(
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly DomainRouterFactory $domainRouterFactory,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['route_name'] === null) {
            throw new MissingRouteNameException();
        }

        $friendlyUrlsByDomain = $this->getFriendlyUrlsIndexedByDomain(
            $options['route_name'],
            (int)$options['entity_id'],
            $options['limit_domains_by_ids'],
        );

        $newUrlsConstraints = [
            new UniqueSlugsOnDomains(
                groups: [self::UNIQUE_SLUGS_VALIDATION_GROUP],
            ),
        ];

        if ($options['required'] && count($friendlyUrlsByDomain) === 0 && $options['entity_id'] === null) {
            $newUrlsConstraints[] = new Count(min: 1, minMessage: 'Please define at least one URL.');
        }

        $builder->add('toDelete', FormType::class);
        $builder->add('mainFriendlyUrlsByDomainId', FormType::class);
        $builder->add('newUrls', CollectionType::class, [
            'entry_type' => FriendlyUrlType::class,
            'required' => false,
            'allow_add' => true,
            'error_bubbling' => false,
            'entry_options' => [
                'limit_domains_by_ids' => $this->domain->getAdminEnabledDomainIds($options['limit_domains_by_ids']),
            ],
            'constraints' => $newUrlsConstraints,
        ]);

        foreach ($friendlyUrlsByDomain as $domainId => $friendlyUrls) {
            $builder->get('toDelete')->add(
                $builder->create((string)$domainId, ChoiceType::class, [
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $friendlyUrls,
                    'choice_label' => 'slug',
                    'choice_value' => 'slug',
                ]),
            );

            $builder->get('mainFriendlyUrlsByDomainId')->add(
                $builder->create((string)$domainId, ChoiceType::class, [
                    'required' => $options['required'],
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => $friendlyUrls,
                    'choice_label' => 'slug',
                    'choice_value' => 'slug',
                    'invalid_message' => 'Previously selected main URL dos not exist any more',
                ]),
            );
        }
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $absoluteUrlsByDomainIdAndSlug = $this->getAbsoluteUrlsIndexedByDomainIdAndSlug(
            $options['route_name'],
            (int)$options['entity_id'],
            $options['limit_domains_by_ids'],
        );
        $mainUrlsSlugsOnDomains = $this->getMainFriendlyUrlSlugsIndexedByDomainId(
            $options['route_name'],
            $options['entity_id'],
            $options['limit_domains_by_ids'],
        );

        $view->vars['absoluteUrlsByDomainIdAndSlug'] = $absoluteUrlsByDomainIdAndSlug;
        $view->vars['routeName'] = $options['route_name'];
        $view->vars['entityId'] = $options['entity_id'];
        $view->vars['mainUrlsSlugsOnDomains'] = $mainUrlsSlugsOnDomains;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => UrlListData::class,
                'required' => false,
                'route_name' => null,
                'entity_id' => null,
                'limit_domains_by_ids' => [],
                'validation_groups' => new GroupSequence(['Default', self::UNIQUE_SLUGS_VALIDATION_GROUP]),
            ])
            ->setAllowedTypes('limit_domains_by_ids', 'array');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[][]
     */
    private function getFriendlyUrlsIndexedByDomain(string $routeName, int $entityId, array $limitDomainsByIds): array
    {
        $friendlyUrlsByDomain = [];

        $friendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameDomainIdsAndEntityIds(
            $routeName,
            $entityId,
            $this->domain->getAdminEnabledDomainIds($limitDomainsByIds),
        );

        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrlsByDomain[$friendlyUrl->getDomainId()][] = $friendlyUrl;
        }

        return $friendlyUrlsByDomain;
    }

    /**
     * @param int[] $limitDomainsByIds
     * @return string[][]
     */
    private function getAbsoluteUrlsIndexedByDomainIdAndSlug(
        string $routeName,
        int $entityId,
        array $limitDomainsByIds,
    ): array {
        $friendlyUrlsByDomain = $this->getFriendlyUrlsIndexedByDomain($routeName, $entityId, $limitDomainsByIds);
        $absoluteUrlsByDomainIdAndSlug = [];

        foreach ($friendlyUrlsByDomain as $domainId => $friendlyUrls) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainId);
            $absoluteUrlsByDomainIdAndSlug[$domainId] = [];

            foreach ($friendlyUrls as $friendlyUrl) {
                $absoluteUrlsByDomainIdAndSlug[$domainId][$friendlyUrl->getSlug()] =
                    $domainRouter->generateByFriendlyUrl(
                        $friendlyUrl,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    );
            }
        }

        return $absoluteUrlsByDomainIdAndSlug;
    }

    /**
     * @return string[]
     */
    private function getMainFriendlyUrlSlugsIndexedByDomainId(
        string $routeName,
        ?int $entityId,
        array $limitDomainsByIds,
    ): array {
        $mainFriendlyUrlsSlugsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomainIds($limitDomainsByIds) as $domainId) {
            if ($entityId === null) {
                $mainFriendlyUrlsSlugsByDomainId[$domainId] = null;

                continue;
            }

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                $routeName,
                $entityId,
            );

            if ($mainFriendlyUrl !== null) {
                $mainFriendlyUrlsSlugsByDomainId[$domainId] = $mainFriendlyUrl->getSlug();
            } else {
                $mainFriendlyUrlsSlugsByDomainId[$domainId] = null;
            }
        }

        return $mainFriendlyUrlsSlugsByDomainId;
    }
}
