<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="chat_agents")
 * @ORM\Entity
 */
class Agent
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $model;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $setup;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $internalKey;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    protected $availableAiFunctions = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     */
    public function __construct(AgentData $agentData)
    {
        $this->setData($agentData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     */
    protected function setData(AgentData $agentData): void
    {
        $this->name = $agentData->name;
        $this->enabled = $agentData->enabled;
        $this->model = $agentData->model;
        $this->setup = $agentData->setup;
        $this->internalKey = $agentData->internalKey;
        $this->availableAiFunctions = $agentData->availableAiFunctions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\AgentData $agentData
     */
    public function edit(AgentData $agentData): void
    {
        $this->setData($agentData);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * @return string
     */
    public function getInternalKey()
    {
        return $this->internalKey;
    }

    /**
     * @return string[]
     */
    public function getAvailableAiFunctions()
    {
        return $this->availableAiFunctions;
    }
}
