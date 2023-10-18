import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useGtmPaymentAndTransportPageViewEvent } from 'gtm/hooks/useGtmPaymentAndTransportPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import Script from 'next/script';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.transport_and_payment);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmPaymentAndTransportPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <Script src="https://widget.packeta.com/v6/www/js/library.js" strategy="afterInteractive" />
            <MetaRobots content="noindex" />
            <TransportAndPaymentContent />
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default TransportAndPaymentPage;
