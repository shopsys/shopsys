<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Newsletter;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class NewsletterMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return bool[]
     */
    public function newsletterSubscribeMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $email = $input['email'];
        $this->newsletterFacade->addSubscribedEmail($email, $this->domain->getId());

        return [
            true,
        ];
    }
}
