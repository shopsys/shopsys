<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelation;

/**
 * The class need the prefix because of the conflicting name in GraphQL generated classes
 *
 * @see https://github.com/overblog/GraphQLBundle/issues/863
 */
class AppPaymentTransportRelation extends PaymentTransportRelation
{
}
