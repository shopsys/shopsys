import { CommonLayout } from 'components/Layout/CommonLayout';
import { CookieConsentContent } from 'components/Pages/CookieConsent/CookieConsentContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';

const CookieConsentPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const breadcrumbs: BreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Cookie consent'), slug: cookieConsentUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.cookie_consent, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Cookie consent update')}>
            <CookieConsentContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default CookieConsentPage;
