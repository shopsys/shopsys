import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useOrderPaymentFailedContentQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type PaymentFailProps = {
    orderUuid: string;
    lastUsedOrderPaymentType: string | undefined;
    paymentTransactionCount: number;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderUuid, lastUsedOrderPaymentType, paymentTransactionCount }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_fail);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: contentData, fetching }] = useOrderPaymentFailedContentQueryApi({ variables: { orderUuid } });

    return (
        <ConfirmationPageContent
            content={contentData?.orderPaymentFailedContent}
            heading={t('Your payment was not successful')}
            isFetching={fetching}
            AdditionalContent={
                <>
                    {lastUsedOrderPaymentType === PaymentTypeEnum.GoPay && (
                        <PaymentsInOrderSelect
                            orderUuid={orderUuid}
                            paymentTransactionCount={paymentTransactionCount}
                        />
                    )}
                </>
            }
        />
    );
};
