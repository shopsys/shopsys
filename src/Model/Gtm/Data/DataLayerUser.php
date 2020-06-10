<?php

declare(strict_types = 1);

namespace App\Model\Gtm\Data;

use App\Model\Gtm\Exception\GtmException;
use DateTime;
use JsonSerializable;

class DataLayerUser implements JsonSerializable
{
    public const TYPE_CUSTOMER = 'Customer';
    public const TYPE_VISITOR = 'Visitor';
    public const TYPE_ADMIN = 'Admin';

    public const STATE_LOGGED_IN = 'Logged In';
    public const STATE_ANONYMOUS = 'Anonymous';

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $state;

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $type User::TYPE_CUSTOMER|User::TYPE_VISITOR|User::TYPE_ADMIN
     */
    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_CUSTOMER, self::TYPE_VISITOR, self::TYPE_ADMIN], true)) {
            throw new GtmException(sprintf('Invalid argument $type "%s"', $type));
        }

        $this->type = $type;
    }

    /**
     * @param string $state self::STATE_ANONYMOUS|self::STATE_LOGGED_IN
     */
    public function setState(string $state): void
    {
        if (!in_array($state, [self::STATE_ANONYMOUS, self::STATE_LOGGED_IN], true)) {
            throw new GtmException(sprintf('Invalid argument $state "%s"', $state));
        }

        $this->state = $state;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $dataToSerialize = [];
        foreach (get_object_vars($this) as $objectName => $objectValue) {
            if ($objectValue instanceof DateTime) {
                $dataToSerialize[$objectName] = $objectValue->format('Y-m-d H:i:s');
            } else {
                $dataToSerialize[$objectName] = $objectValue;
            }
        }

        return array_filter($dataToSerialize);
    }
}
