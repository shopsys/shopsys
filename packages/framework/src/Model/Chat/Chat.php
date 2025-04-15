<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;

/**
 * @ORM\Table(name="chats")
 * @ORM\Entity
 */
class Chat
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
     * @ORM\Column(type="guid", unique=true)
     */
    protected $userIdentifier;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage", mappedBy="chat", cascade={"remove"})
     */
    protected $messages;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Chat\Agent\Agent")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $agent;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatData $chatData
     */
    public function __construct(ChatData $chatData)
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->setData($chatData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatData $chatData
     */
    protected function setData(ChatData $chatData): void
    {
        $this->userIdentifier = $chatData->userIdentifier;
        $this->agent = $chatData->agent;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage[]
     */
    public function getMessages()
    {
        return $this->messages->getValues();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    public function addMessage(ChatMessage $message): void
    {
        $this->messages->add($message);
        $this->resetUpdatedAt();
    }

    public function resetUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @return string
     */
    public function getWholeCommunication(): string
    {
        $output = [
            'Agent: ' . $this->getAgent()->getName(),
        ];

        foreach ($this->getMessages() as $message) {
            $output[] = sprintf('Question: %s', $message->getQuestion());
            $output[] = $message->getAnswer() ? sprintf('Answer: %s', $message->getAnswer()) : '';
        }

        return implode("\n", $output);
    }
}
