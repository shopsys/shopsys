import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import {
    TransportAndPaymentFormType,
    useTransportAndPaymentForm,
    useTransportAndPaymentFormMeta,
} from 'components/Pages/Order/TransportAndPayment/formMeta';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Footer from 'components/Layout/Footer';
import { getTransports } from 'connectors/transports/Transports';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const TransportAndPayment: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const { cartUuid } = useShopsysSelector((state) => state.cartInput);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = useGetInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );
    const transports = getTransports(cartUuid);

    const t = useTypedTranslationFunction();
    const [formProviderMethods] = useTransportAndPaymentForm();
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    const onSelectTransportAndPaymentHandler: SubmitHandler<TransportAndPaymentFormType> = () => {
        event?.preventDefault();
        router.push(contactInformationUrl);
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
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
                <Footer />
            </Webline>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const redirect = handleOrderPagesRedirect(context);
    return redirect === false ? initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]) : redirect;
});

export default TransportAndPayment;
