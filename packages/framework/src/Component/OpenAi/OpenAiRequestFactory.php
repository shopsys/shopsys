<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Shopsys\FrameworkBundle\Model\Chat\Chat;

class OpenAiRequestFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return array
     */
    public function getOpenAiSimpleRequest(Chat $chat): array
    {
        $request = [];
        $request['model'] = $chat->getAgent()->getModel();
        $request['messages'] = $this->getMessages($chat);

        return $request;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return array
     */
    protected function getMessages(Chat $chat): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $chat->getAgent()->getSetup(),
            ],
        ];

        foreach ($chat->getMessages() as $message) {
            $messages[] = [
                'role' => 'user',
                'content' => $message->getQuestion(),
            ];

            if ($message->getAnswer()) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $message->getAnswer(),
                ];
            }
        }

        return $messages;
    }
}
