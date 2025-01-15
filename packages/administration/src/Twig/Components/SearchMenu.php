<?php

namespace Shopsys\AdministrationBundle\Twig\Components;

use Knp\Menu\ItemInterface;
use Normalizer;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class SearchMenu
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    public function __construct(
        private readonly SideMenuBuilder $sideMenuBuilder
    ) {

    }

    #[LiveAction]
    public function close(): void
    {
        $this->search = '';
    }

    public function getResults(): array
    {
        if ($this->search === '') {
            return [];
        }

        return $this->buildResults($this->search);
    }

    private function buildResults(string $search): array
    {
        $menu = $this->sideMenuBuilder->createMenu();

        $results = [];
        $this->buildResultsList($results, $menu, $search);

        return $results;
    }

    /**
     * @param array $results
     * @param \Knp\Menu\ItemInterface $item
     * @param string $searchString
     */
    private function buildResultsList(array &$results, ItemInterface $item, string $searchString): void
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
    private function containsLabelSearchString(string $label, string $searchString): bool
    {
        return str_contains($this->convertStringWithDiacritics($label), $this->convertStringWithDiacritics($searchString));
    }

    /**
     * @param string $string
     * @return string
     */
    private function convertStringWithDiacritics(string $string): string
    {
        return strtolower(preg_replace('~[\p{M}-]+~u', '', Normalizer::normalize($string, Normalizer::FORM_D)));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item
     * @return array
     */
    private function buildPathByItem(ItemInterface $item): array
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