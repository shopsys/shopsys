<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Dto;

enum ChatRoleEnum: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';

    case FUNCTION = 'function';
}
