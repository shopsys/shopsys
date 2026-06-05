import { WalletIcon } from 'components/Basic/Icon/WalletIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentInProcessProps = {
    orderPaymentInProcessContent: string;
    paymentInstructionUrl: string | null;
};

export const PaymentInProcess: FC<PaymentInProcessProps> = ({
    orderPaymentInProcessContent,
    paymentInstructionUrl,
}) => {
    const { t } = useTranslation();

    const paymentInstructionActionProps = paymentInstructionUrl
        ? {
              actionHref: paymentInstructionUrl,
              actionSkeletonType: 'order-confirmation' as const,
              actionTitle: t('Show payment instruction'),
          }
        : {};

    return (
        <ConfirmationPageContent
            content={orderPaymentInProcessContent}
            heading={t('The payment is being processed')}
            headingIcon={WalletIcon}
            headingVariant="info"
            {...paymentInstructionActionProps}
        />
    );
};
