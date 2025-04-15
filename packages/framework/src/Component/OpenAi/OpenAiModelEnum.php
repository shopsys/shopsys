<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

class OpenAiModelEnum
{
    public const string GPT_3_5_TURBO = 'gpt-3.5-turbo';

    public const string GPT_4_1_MINI = 'gpt-4.1-mini';

    public const ALL = [
        self::GPT_3_5_TURBO,
        self::GPT_4_1_MINI,
    ];
}
