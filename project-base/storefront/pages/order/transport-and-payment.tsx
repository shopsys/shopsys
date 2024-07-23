import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import {
    AdvertsQueryDocument,
    TypeAdvertsQueryVariables,
} from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmPaymentAndTransportPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPaymentAndTransportPageViewEvent';
import Script from 'next/script';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

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
            initServerSideProps<TypeAdvertsQueryVariables>({
                context,
                redisClient,
                domainConfig,
                t,
                prefetchedQueries: [
                    {
                        query: AdvertsQueryDocument,
                        variables: {
                            positionNames: ['cartPreview'],
                            categoryUuid: null,
                        },
                    },
                ],
            }),
);

export default TransportAndPaymentPage;
