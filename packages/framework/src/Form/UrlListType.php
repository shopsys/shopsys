<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomain;
use Shopsys\FrameworkBundle\Form\Exception\MissingRouteNameException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * URL addresses of an entity on a single domain, multidomain entities render one instance per domain via MultidomainType
 */
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

        $friendlyUrls = $this->getFriendlyUrls($options);

        // the choice fields make sense only once the entity has URL addresses to choose from
        if (count($friendlyUrls) > 0) {
            $builder
                ->add('toDelete', ChoiceType::class, [
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $friendlyUrls,
                    'choice_label' => 'slug',
                    'choice_value' => 'slug',
                ])
                ->add('mainFriendlyUrl', ChoiceType::class, [
                    'required' => $options['required'],
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => $friendlyUrls,
                    'choice_label' => 'slug',
                    'choice_value' => 'slug',
                    'invalid_message' => 'Previously selected main URL dos not exist any more',
                ]);
        }

        $builder->add('newUrls', CollectionType::class, [
            'entry_type' => FriendlyUrlType::class,
            'required' => false,
            'allow_add' => true,
            'error_bubbling' => false,
            'constraints' => $this->getNewUrlsConstraints($options, $friendlyUrls),
        ]);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domainUrl'] = $this->domain->getDomainConfigById($options['domain_id'])->getUrl();
        $view->vars['absoluteUrlsBySlug'] = $this->getAbsoluteUrlsIndexedBySlug($options);
        $view->vars['mainUrlSlug'] = $this->findMainFriendlyUrlSlug($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setDefaults([
                'data_class' => UrlListData::class,
                // the entity data always hold an instance, even when the administrator has not touched the URL addresses
                'empty_data' => static fn (): UrlListData => new UrlListData(),
                'required' => false,
                'route_name' => null,
                'entity_id' => null,
                'validation_groups' => new GroupSequence(['Default', self::UNIQUE_SLUGS_VALIDATION_GROUP]),
            ])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('route_name', ['string', 'null'])
            ->setAllowedTypes('entity_id', ['int', 'null'])
            ->setInfo('entity_id', 'Null means a new entity without any URL addresses yet.');
    }

    /**
     * @param array<string, mixed> $options
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[] $friendlyUrls
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getNewUrlsConstraints(array $options, array $friendlyUrls): array
    {
        $newUrlsConstraints = [
            new UniqueSlugsOnDomain(
                domainId: $options['domain_id'],
                groups: [self::UNIQUE_SLUGS_VALIDATION_GROUP],
            ),
        ];

        if ($options['required'] && count($friendlyUrls) === 0 && $options['entity_id'] === null) {
            $newUrlsConstraints[] = new Callback(callback: [$this, 'validateAtLeastOneNewUrl']);
        }

        return $newUrlsConstraints;
    }

    /**
     * @param array<int, array<string, string>> $newUrls
     */
    public function validateAtLeastOneNewUrl(array $newUrls, ExecutionContextInterface $context): void
    {
        if (count($newUrls) > 0) {
            return;
        }

        $context->addViolation('Please define at least one URL.');
    }

    /**
     * @param array<string, mixed> $options
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    private function getFriendlyUrls(array $options): array
    {
        if ($options['entity_id'] === null) {
            return [];
        }

        return $this->friendlyUrlFacade->getAllByRouteNameDomainIdsAndEntityIds(
            $options['route_name'],
            $options['entity_id'],
            [$options['domain_id']],
        );
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, string>
     */
    private function getAbsoluteUrlsIndexedBySlug(array $options): array
    {
        $domainRouter = $this->domainRouterFactory->getRouter($options['domain_id']);
        $absoluteUrlsBySlug = [];

        foreach ($this->getFriendlyUrls($options) as $friendlyUrl) {
            $absoluteUrlsBySlug[$friendlyUrl->getSlug()] = $domainRouter->generateByFriendlyUrl(
                $friendlyUrl,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }

        return $absoluteUrlsBySlug;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function findMainFriendlyUrlSlug(array $options): ?string
    {
        if ($options['entity_id'] === null) {
            return null;
        }

        return $this->friendlyUrlFacade->findMainFriendlyUrl(
            $options['domain_id'],
            $options['route_name'],
            $options['entity_id'],
        )?->getSlug();
    }
}
