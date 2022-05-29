import MetaRobots from 'components/Basic/Head/MetaRobots';
import OrderAction from 'components/Blocks/OrderAction';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import Footer from 'components/Layout/Footer';
import OrderLayout from 'components/Layout/OrderLayout';
import Webline from 'components/Layout/Webline';
import {
    useTransportAndPaymentForm,
    useTransportAndPaymentFormMeta,
} from 'components/Pages/Order/TransportAndPayment/formMeta';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import { useTransports } from 'connectors/transports/Transports';
import { LastOrderFragmentApi, useLastOrderQueryApi } from 'graphql/generated';
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

export const TransportAndPayment: FC<TransportAndPaymentProps> = () => {
    const router = useRouter();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );
    const transports = useTransports(cartUuid);
    const [{ data }] = useLastOrderQueryApi({ requestPolicy: 'network-only' });

    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useTransportAndPaymentForm(data?.lastOrder);
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    const onSelectTransportAndPaymentHandler: SubmitHandler<TransportAndPaymentFormType> = () => {
        router.push(contactInformationUrl);
    };

    const pickupPlace: PickupPlaceType | null = useMemo(
        () =>
            data?.lastOrder?.pickupPlaceIdentifier !== undefined && data.lastOrder.pickupPlaceIdentifier !== null
                ? {
                      identifier: data.lastOrder.pickupPlaceIdentifier,
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
        [data?.lastOrder?.pickupPlaceIdentifier],
    );

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <form onSubmit={formProviderMethods.handleSubmit(onSelectTransportAndPaymentHandler)}>
                <FormProvider {...formProviderMethods}>
                    <OrderLayout activeStep={2} buttonNextText={t('Contact information')}>
                        {transports.length > 0 && (
                            <Select transports={transports} preselectedPickupPlace={pickupPlace} />
                        )}
                        <OrderAction
                            activeStep={2}
                            buttonBack={t('Back')}
                            buttonNext={t('Contact information')}
                            hasDisabledLook={!formProviderMethods.formState.isValid}
                            withGapTop={true}
                            withGapBottom={true}
                            buttonBackLink={cartUrl}
                        />
                    </OrderLayout>
                </FormProvider>
            </form>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
            />
            <Webline type="dark">
                <Footer simpleFooter />
            </Webline>
        </StaticUrlGuard>
    );
};
