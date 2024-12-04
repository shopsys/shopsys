import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TypePaymentTypeEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

type PaymentFailProps = {
    orderUuid: string;
    lastUsedOrderPaymentType: TypePaymentTypeEnum | undefined;
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
        <ConfirmationPageContent content={orderPaymentFailedContent} heading={t('Your payment was not successful')}>
            <>
                {lastUsedOrderPaymentType === TypePaymentTypeEnum.GoPay && (
                    <PaymentsInOrderSelect
                        className="mt-6"
                        orderUuid={orderUuid}
                        paymentTransactionCount={paymentTransactionCount}
                    />
                )}
            </>
        </ConfirmationPageContent>
    );
};
