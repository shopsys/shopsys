import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderLayout } from 'components/Layout/OrderLayout';
import { ContactInformationContent } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import {
    AdvertsQueryDocument,
    TypeAdvertsQueryVariables,
} from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { CountriesQueryDocument } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmContactInformationViewEvent } from 'gtm/utils/pageReadyEvents/useGtmContactInformationViewEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.contact_information);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);
    useGtmContactInformationViewEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <OrderLayout page="contact-information">
                <ContactInformationContent />
            </OrderLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps<TypeAdvertsQueryVariables>({
                context,
                currentCustomerUserPrefetchMode: 'full',
                redisClient,
                domainConfig,
                t,
                authenticationConfig: {
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
                prefetchedQueries: [
                    { query: CountriesQueryDocument },
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

export default ContactInformationPage;
