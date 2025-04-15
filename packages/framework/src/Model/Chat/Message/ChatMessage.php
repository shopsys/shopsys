<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Message;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Chat\Chat;

/**
 * @ORM\Table(name="chat_messages")
 * @ORM\Entity
 */
class ChatMessage
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
     * @var \Shopsys\FrameworkBundle\Model\Chat\Chat
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Chat\Chat", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, name="chat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $chat;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $question;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $answer = null;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $inputTokens = null;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $outputTokens = null;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $totalTokens = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     */
    public function __construct(
        Chat $chat,
        string $question,
    ) {
        $this->chat = $chat;
        $this->question = $question;
        $this->createdAt = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer($answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return int|null
     */
    public function getInputTokens()
    {
        return $this->inputTokens;
    }

    /**
     * @param int|null $inputTokens
     */
    public function setInputTokens($inputTokens): void
    {
        $this->inputTokens = $inputTokens;
    }

    /**
     * @return int|null
     */
    public function getOutputTokens()
    {
        return $this->outputTokens;
    }

    /**
     * @param int|null $outputTokens
     */
    public function setOutputTokens($outputTokens): void
    {
        $this->outputTokens = $outputTokens;
    }

    /**
     * @return int|null
     */
    public function getTotalTokens()
    {
        return $this->totalTokens;
    }

    /**
     * @param int|null $totalTokens
     */
    public function setTotalTokens($totalTokens): void
    {
        $this->totalTokens = $totalTokens;
    }
}
