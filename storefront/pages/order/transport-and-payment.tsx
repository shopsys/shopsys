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
import { createClient } from 'helpers/createClient';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useGtmPaymentShippingView } from 'hooks/gtm/useGtmPaymentShippingView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { TransportAndPaymentFormType } from 'types/form';
import { ssrExchange } from 'urql';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const TransportAndPayment: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );
    const transports = useTransports(cartUuid);

    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useTransportAndPaymentForm();
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('step2');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmPaymentShippingView(gtmStaticPageViewEvent);

    const onSelectTransportAndPaymentHandler: SubmitHandler<TransportAndPaymentFormType> = () => {
        event?.preventDefault();
        router.push(contactInformationUrl);
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <form onSubmit={formProviderMethods.handleSubmit(onSelectTransportAndPaymentHandler)}>
                <FormProvider {...formProviderMethods}>
                    <OrderLayout activeStep={2} buttonNextText={t('Contact information')}>
                        {transports.length > 0 && <Select transports={transports} />}
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const ssrCache = ssrExchange({ isClient: false });
    const client = createClient(context, store, ssrCache);
    const redirect = await handleOrderPagesRedirect(context, store, client);
    return redirect === false ? initServerSideProps(context, store, false, [], client, ssrCache) : redirect;
});

export default TransportAndPayment;
