<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\TreeSelection;

use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TreeSelectionBranchJsonDataHelper
{
    public function __construct(protected readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return array<int, array{id: int, label: string|null, isVisible: bool, isExpandable: bool, loadUrl: string}>
     */
    public function createJsonData(
        TreeSelectionEntityInterface $rootEntity,
        ?int $domainId,
        string $routeName,
    ): array {
        $childrenData = [];

        foreach ($rootEntity->getChildren() as $child) {
            $childrenData[] = [
                'id' => $child->getId(),
                'label' => $child->getName(),
                'isVisible' => $domainId === null || $child->isVisible($domainId),
                'isExpandable' => $child->hasChildren(),
                'loadUrl' => $this->urlGenerator->generate($routeName, [
                    'domainId' => $domainId,
                    'id' => $child->getId(),
                ]),
            ];
        }

        return $childrenData;
    }
}
