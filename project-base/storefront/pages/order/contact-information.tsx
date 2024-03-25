import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { SkeletonOrderContent } from 'components/Blocks/Skeleton/SkeletonOrderContent';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { ContactInformationWrapper } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import { CountriesQueryDocument } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useOrderPagesAccess } from 'hooks/cart/useOrderPagesAccess';

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const canContentBeDisplayed = useOrderPagesAccess('contact-information');

    return (
        <>
            <MetaRobots content="noindex" />

            <OrderLayout>
                {canContentBeDisplayed ? <ContactInformationWrapper /> : <SkeletonOrderContent />}
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
