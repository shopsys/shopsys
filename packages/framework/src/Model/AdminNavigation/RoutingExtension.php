<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;
use Override;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Menu factory extension similar to the original RoutingExtension, but it ignores missing parameters in routes
 * In our menu there are defined non-displayable items without specified mandatory parameters such as "New customer"
 * They are still important for resolving the correct current item and rendering the breadcrumb navigation
 * Having them rendered as a link is not important as they typically represent a number of pages (eg. editing ANY product)
 *
 * @see \Knp\Menu\Integration\Symfony\RoutingExtension
 */
class RoutingExtension implements ExtensionInterface
{
    public const string ROUTE_NAME_EXTRA = 'route_name';

    public function __construct(protected readonly UrlGeneratorInterface $generator)
    {
    }

    /**
     * {@inheritdoc}
     * Inspired by @see \Knp\Menu\Integration\Symfony\RoutingExtension::buildOptions()
     */
    #[Override]
    public function buildOptions(array $options = []): array
    {
        if (!empty($options['route'])) {
            $params = $options['routeParameters'] ?? [];
            $absolute = isset($options['routeAbsolute']) && $options['routeAbsolute'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

            try {
                $options['uri'] = $this->generator->generate($options['route'], $params, $absolute);
            } catch (MissingMandatoryParametersException $e) {
                $options['uri'] = null;
            }

            $options['extras']['routes'][] = [
                'route' => $options['route'],
                'parameters' => $params,
            ];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildItem(ItemInterface $item, array $options): void
    {
        if (array_key_exists('route', $options)) {
            $item->setExtra(static::ROUTE_NAME_EXTRA, $options['route']);
        }
    }
}
