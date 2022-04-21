<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\Model\Transport\TransportFacade;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExistingTransportValidator extends ConstraintValidator
{
    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private TransportFacade $transportFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     */
    public function __construct(TransportFacade $transportFacade, Domain $domain)
    {
        $this->transportFacade = $transportFacade;
        $this->domain = $domain;
    }

    /**
     * @param string|null $value
     * @param \App\FrontendApi\Model\Component\Constraints\ExistingTransport $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExistingTransport) {
            throw new UnexpectedTypeException($constraint, ExistingTransport::class);
        }
        if ($value === null) {
            return;
        }
        try {
            $this->transportFacade->getEnabledOnDomainByUuid($value, $this->domain->getId());
        } catch (TransportNotFoundException $exception) {
            $this->context->buildViolation($constraint->invalidMessage)
                ->setCode($constraint::TRANSPORT_DOES_NOT_EXIST_ERROR)
                ->addViolation();
        }
    }
}
