import OrderAction from 'components/Blocks/OrderAction';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import {
    useTransportAndPaymentForm,
    useTransportAndPaymentFormMeta,
} from 'components/Pages/Order/TransportAndPayment/formMeta';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import { LastOrderFragmentApi } from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { TransportAndPaymentFormType } from 'types/form';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type TransportAndPaymentProps = {
    transports: TransportType[];
    lastOrder: LastOrderFragmentApi | null;
};

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ transports, lastOrder }) => {
    const router = useRouter();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );

    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useTransportAndPaymentForm(lastOrder);
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    const onSelectTransportAndPaymentHandler: SubmitHandler<TransportAndPaymentFormType> = () => {
        router.push(contactInformationUrl);
    };

    const pickupPlace: PickupPlaceType | null = useMemo(
        () =>
            lastOrder?.pickupPlaceIdentifier !== undefined && lastOrder.pickupPlaceIdentifier !== null
                ? {
                      identifier: lastOrder.pickupPlaceIdentifier,
                      name: '', // pickup place from the last order
                      city: '',
                      country: {
                          name: '',
                          code: '',
                      },
                      description: '',
                      openingHoursHtml: '',
                      postcode: '',
                      street: '',
                  }
                : null,
        [lastOrder?.pickupPlaceIdentifier],
    );

    return (
        <>
            <form onSubmit={formProviderMethods.handleSubmit(onSelectTransportAndPaymentHandler)}>
                <FormProvider {...formProviderMethods}>
                    {transports.length > 0 && <Select transports={transports} preselectedPickupPlace={pickupPlace} />}
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
