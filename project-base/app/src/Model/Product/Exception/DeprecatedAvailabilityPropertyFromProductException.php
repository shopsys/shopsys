<?php

declare(strict_types=1);

namespace App\Model\Product\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;

class DeprecatedAvailabilityPropertyFromProductException extends Exception
{
    /**
     * @param string $availabilityPropertyName
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null $availability
     */
    public function __construct(string $availabilityPropertyName, private ?Availability $availability = null)
    {
        $message = sprintf('Deprecated %s property.', $availabilityPropertyName);

        parent::__construct($message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     */
    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }
}
