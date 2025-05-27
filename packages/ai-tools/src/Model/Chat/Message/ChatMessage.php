<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Message;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\AiToolsBundle\Model\Chat\Chat;

/**
 * @ORM\Table(name="chat_messages")
 * @ORM\Entity
 */
class ChatMessage
{
    public const TYPE_MESSAGE = 'message';
    public const TYPE_FUNCTION = 'function';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\Chat
     * @ORM\ManyToOne(targetEntity="Shopsys\AiToolsBundle\Model\Chat\Chat", inversedBy="messages")
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
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    protected $functionCall = null;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    protected $functionCallResult = null;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $type;

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Chat $chat
     * @param string $question
     */
    public function __construct(
        Chat $chat,
        string $question,
    ) {
        $this->chat = $chat;
        $this->question = $question;
        $this->createdAt = new DateTime();
        $this->type = self::TYPE_MESSAGE;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\Chat
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

    /**
     * @return array|null
     */
    public function getFunctionCall()
    {
        return $this->functionCall;
    }

    /**
     * @param array $functionCall
     */
    public function setFunctionCall($functionCall)
    {
        $this->functionCall = $functionCall;
    }

    /**
     * @return array|null
     */
    public function getFunctionCallResult()
    {
        return $this->functionCallResult;
    }

    /**
     * @param array|null $functionCallResult
     */
    public function setFunctionCallResult($functionCallResult)
    {
        $this->functionCallResult = $functionCallResult;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }
}
