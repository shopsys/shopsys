import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import {
    AdvertsQueryDocument,
    TypeAdvertsQueryVariables,
} from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useGtmPaymentAndTransportViewEvent } from 'gtm/utils/pageReadyEvents/useGtmPaymentAndTransportViewEvent';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.transport_and_payment);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);
    useGtmPaymentAndTransportViewEvent(gtmStaticPageReadyEvent);

    return (
        <>
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
                currentCustomerUserPrefetchMode: 'full',
                authenticationConfig: {
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
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
