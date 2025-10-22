<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class WithdrawalMailTemplateVariablesProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    public function create(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(
            t('Withdrawal from contract'),
        );

        return $mailTemplateVariables
            ->addVariable(
                WithdrawalMail::VARIABLE_NUMBER,
                t('Order number'),
            )
            ->addVariable(
                WithdrawalMail::VARIABLE_ORDER_DETAIL_URL,
                t('Order detail URL address'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                WithdrawalMail::VARIABLE_PRODUCTS,
                t('List of products in order (image, name, quantity, price per unit including VAT, total price per item including VAT)'),
                MailTemplateVariables::CONTEXT_BODY,
            );
    }
}
