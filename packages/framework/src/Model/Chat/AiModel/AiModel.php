<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\AiModel;

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
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default":true})
     */
    protected $isActive;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $isDeprecated;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelApiSourceEnum
     * @ORM\Column(
     *     type="string",
     *     enumType=\Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelApiSourceEnum::class
     * )
     */
    protected $apiSource;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelData $aiModelData
     */
    public function __construct(AiModelData $aiModelData)
    {
        $this->setData($aiModelData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelData $aiModelData
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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool|null
     */
    public function isDeprecated(): ?bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param AiModelData $aiModelData
     * @return void
     */
    public function edit(AiModelData $aiModelData)
    {
        $this->setData($aiModelData);
    }

}
