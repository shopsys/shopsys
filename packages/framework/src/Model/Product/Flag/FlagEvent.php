<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Symfony\Contracts\EventDispatcher\Event;

class FlagEvent extends Event
{
    /**
     * The CREATE event occurs once a flag was created.
     *
     * This event allows you to run jobs dependent on the flag creation.
     */
    public const CREATE = 'flag.create';
    /**
     * The UPDATE event occurs once a flag was changed.
     *
     * This event allows you to run jobs dependent on the flag change.
     */
    public const UPDATE = 'flag.update';
    /**
     * The DELETE event occurs once a flag was deleted.
     *
     * This event allows you to run jobs dependent on the flag deletion.
     */
    public const DELETE = 'flag.delete';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    protected $flag;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     */
    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function getFlag(): Flag
    {
        return $this->flag;
    }
}
