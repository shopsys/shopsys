import { CommonLayout } from 'components/Layout/CommonLayout';
import { RegistrationContent } from 'components/Pages/Registration/RegistrationContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const RegistrationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);
    const breadcrumbs: BreadcrumbFragment[] = [{ __typename: 'Link', name: t('Registration'), slug: registrationUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Registration')}>
            <RegistrationContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default RegistrationPage;
