import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CustomerContent } from 'components/Pages/Customer/CustomerContent';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'gtm/types/enums';

const CustomerPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Customer'), slug: customerUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Customer')}>
                <CustomerContent breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, authenticationRequired: true, redisClient, domainConfig, t }),
);

export default CustomerPage;
