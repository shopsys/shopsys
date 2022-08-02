import OrderAction from 'components/Blocks/OrderAction';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import {
    useTransportAndPaymentForm,
    useTransportAndPaymentFormMeta,
} from 'components/Pages/Order/TransportAndPayment/formMeta';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import { LastOrderFragmentApi, useLastOrderQueryApi, useStoreQueryApi } from 'graphql/generated';
import { getPacketeryCookie } from 'helpers/packetery';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { CurrentCartType } from 'types/cart';
import { TransportAndPaymentFormType } from 'types/form';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { getGtmPickupPlaceFromLastOrder, getGtmPickupPlaceFromStore } from 'utils/Gtm/Mappers';

type TransportAndPaymentProps = {
    transports: TransportType[];
    lastOrder: LastOrderFragmentApi | null;
    currentCart: CurrentCartType;
};

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ transports, lastOrder, currentCart }) => {
    const router = useRouter();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );
    const [{ data }] = useLastOrderQueryApi({ requestPolicy: 'network-only' });
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useTransportAndPaymentForm(currentCart, lastOrder);
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const [{ data: pickupPlaceData }] = useStoreQueryApi({
        pause: data?.lastOrder?.pickupPlaceIdentifier === undefined || data.lastOrder.pickupPlaceIdentifier === null,
        variables: { uuid: data?.lastOrder?.pickupPlaceIdentifier ?? null },
    });

    const onSelectTransportAndPaymentHandler: SubmitHandler<TransportAndPaymentFormType> = () => {
        router.push(contactInformationUrl);
    };

    const pickupPlace: PickupPlaceType | null = useMemo(() => {
        if (data?.lastOrder?.pickupPlaceIdentifier === undefined || data.lastOrder.pickupPlaceIdentifier === null) {
            return null;
        }

        const packeteryCookie = getPacketeryCookie();

        if (packeteryCookie?.identifier === data.lastOrder.pickupPlaceIdentifier) {
            return packeteryCookie;
        }

        if (pickupPlaceData?.store !== undefined && pickupPlaceData.store !== null) {
            return getGtmPickupPlaceFromStore(data.lastOrder.pickupPlaceIdentifier, pickupPlaceData.store);
        }

        return getGtmPickupPlaceFromLastOrder(data.lastOrder.pickupPlaceIdentifier, data.lastOrder);
    }, [data?.lastOrder, pickupPlaceData?.store]);

    return (
        <>
            <form onSubmit={formProviderMethods.handleSubmit(onSelectTransportAndPaymentHandler)}>
                <FormProvider {...formProviderMethods}>
                    {transports.length > 0 && <Select transports={transports} lastOrderPickupPlace={pickupPlace} />}
                    <OrderAction
                        activeStep={2}
                        buttonBack={t('Back')}
                        buttonNext={t('Contact information')}
                        hasDisabledLook={!formProviderMethods.formState.isValid}
                        withGapTop={true}
                        withGapBottom={true}
                        buttonBackLink={cartUrl}
                    />
                </FormProvider>
            </form>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
            />
        </>
    );
};
