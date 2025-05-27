<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ai_models")
 * @ORM\Entity
 */
class AiModel
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isDeprecated;

    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum
     * @ORM\Column(
     *     type="string",
     *     enumType=\Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum::class
     * )
     */
    protected $apiSource;

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     */
    public function __construct(AiModelData $aiModelData)
    {
        $this->setData($aiModelData);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     */
    public function setData(AiModelData $aiModelData)
    {
        $this->id = $aiModelData->id;
        $this->name = $aiModelData->name;
        $this->description = $aiModelData->description;
        $this->isActive = $aiModelData->isActive;
        $this->isDeprecated = $aiModelData->isDeprecated;
        $this->apiSource = $aiModelData->apiSource;
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
    public function isDeprecated()
    {
        return $this->isDeprecated;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum
     */
    public function getApiSource()
    {
        return $this->apiSource;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData $aiModelData
     */
    public function edit(AiModelData $aiModelData)
    {
        $this->setData($aiModelData);
    }
}
