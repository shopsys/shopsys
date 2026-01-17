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
    public function __construct(
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return bool[]
     */
    public function newsletterSubscribeMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $email = $input['email'];
        $this->newsletterFacade->addSubscribedEmailIfNotExists($email, $this->domain->getId());

        return [
            true,
        ];
    }
}
