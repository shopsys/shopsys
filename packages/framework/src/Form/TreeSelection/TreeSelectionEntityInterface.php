<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\TreeSelection;

interface TreeSelectionEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @return \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[]
     */
    public function getChildren();

    /**
     * @return bool
     */
    public function isVisible(int $domainId);
}
