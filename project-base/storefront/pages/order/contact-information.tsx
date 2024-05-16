import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { ContactInformationWrapper } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import { CountriesQueryDocument } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmContactInformationPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmContactInformationPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact_information);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmContactInformationPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <OrderLayout page="contact-information">
                <ContactInformationWrapper />
            </OrderLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                redisClient,
                domainConfig,
                t,
                prefetchedQueries: [{ query: CountriesQueryDocument }],
            }),
);

export default ContactInformationPage;
