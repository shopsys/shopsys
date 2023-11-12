import { GoPayGateway } from './Gateways/GoPayGateway';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { useOrderPaymentFailedContentQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type PaymentFailProps = {
    orderUuid: string;
    orderPaymentType: string | undefined;
    canPaymentBeRepeated: boolean;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderUuid, orderPaymentType, canPaymentBeRepeated }) => {
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
                orderPaymentType === PaymentTypeEnum.GoPay && canPaymentBeRepeated ? (
                    <GoPayGateway
                        requiresAction
                        className="mt-5"
                        initialButtonText={t('Repeat payment')}
                        orderUuid={orderUuid}
                    />
                ) : undefined
            }
        />
    );
};
