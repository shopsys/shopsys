<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Knp\Menu\ItemInterface;
use Normalizer;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchAdminController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder $sideMenuBuilder
     */
    public function __construct(
        protected readonly SideMenuBuilder $sideMenuBuilder,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/search/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function searchAction(Request $request): Response
    {
        $searchString = $request->query->getString('search');
        $menu = $this->sideMenuBuilder->createMenu();

        $results = [];
        $this->buildResultsList($results, $menu, $searchString);

        return $this->render(
            '@ShopsysAdministration/partial/search_result.html.twig',
            [
                'results' => $results,
            ],
        );
    }

    /**
     * @param array $results
     * @param \Knp\Menu\ItemInterface $item
     * @param string $searchString
     */
    protected function buildResultsList(array &$results, ItemInterface $item, string $searchString): void
    {
        if ($item->getLabel() && $item->getUri() && $this->containsLabelSearchString($item->getLabel(), $searchString)) {
            $results[] = [
                'uri' => $item->getUri(),
                'pathLabels' => $this->buildPathByItem($item),
                'label' => $item->getLabel(),
            ];
        }

        foreach ($item->getChildren() as $child) {
            $this->buildResultsList($results, $child, $searchString);
        }
    }

    /**
     * @param string $label
     * @param string $searchString
     * @return bool
     */
    protected function containsLabelSearchString(string $label, string $searchString): bool
    {
        return str_contains($this->convertStringWithDiacritics($label), $this->convertStringWithDiacritics($searchString));
    }

    /**
     * @param string $string
     * @return string
     */
    protected function convertStringWithDiacritics(string $string): string
    {
        return strtolower(preg_replace('~[\p{M}-]+~u', '', Normalizer::normalize($string, Normalizer::FORM_D)));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @return array
     */
    protected function buildPathByItem(ItemInterface $item): array
    {
        $itemLabels = [$item->getLabel()];

        while ($item->getParent()) {
            $item = $item->getParent();

            if ($item->getLabel() !== 'root') {
                $itemLabels = array_merge([$item->getLabel()], $itemLabels);
            }
        }

        return $itemLabels;
    }
}
