<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlagsExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected $flagFacade;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]|null
     */
    protected $allFlags;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    public function __construct(
        FlagFacade $flagFacade,
        EngineInterface $templating
    ) {
        $this->flagFacade = $flagFacade;
        $this->templating = $templating;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'renderFlagsByIds',
                function (array $flagIds, string $classAddition = '') {
                    return $this->templating->render('@ShopsysShop/Front/Inline/Product/productFlags.html.twig', [
                        'flags' => $this->getFlagsByIds($flagIds),
                        'classAddition' => $classAddition,
                    ]);
                },
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param int[] $flagIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getFlagsByIds(array $flagIds): array
    {
        if ($this->allFlags == null) {
            $this->allFlags = $this->flagFacade->getAll();
        }

        $flags = [];
        foreach ($this->allFlags as $flag) {
            if (in_array($flag->getId(), $flagIds, true)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }
}
