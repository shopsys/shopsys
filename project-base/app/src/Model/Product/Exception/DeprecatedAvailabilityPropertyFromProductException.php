<?php

declare(strict_types=1);

namespace App\Model\Product\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;

class DeprecatedAvailabilityPropertyFromProductException extends Exception
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     */
    private $availability;

    /**
     * @param string $availabilityPropertyName
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null $availability
     */
    public function __construct(string $availabilityPropertyName, ?Availability $availability)
    {
        $this->availability = $availability;
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
