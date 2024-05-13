import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type PaymentFailProps = {
    orderUuid: string;
    lastUsedOrderPaymentType: string | undefined;
    paymentTransactionCount: number;
    orderPaymentFailedContent: string;
};

export const PaymentFail: FC<PaymentFailProps> = ({
    orderUuid,
    lastUsedOrderPaymentType,
    paymentTransactionCount,
    orderPaymentFailedContent,
}) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_fail);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <ConfirmationPageContent
            content={orderPaymentFailedContent}
            heading={t('Your payment was not successful')}
            AdditionalContent={
                <>
                    {lastUsedOrderPaymentType === PaymentTypeEnum.GoPay && (
                        <PaymentsInOrderSelect
                            className="mt-6"
                            orderUuid={orderUuid}
                            paymentTransactionCount={paymentTransactionCount}
                        />
                    )}
                </>
            }
        />
    );
};
