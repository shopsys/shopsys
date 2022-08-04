import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import { CookieConsentPage } from 'components/Pages/CookieConsent/CookieConsentPage';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const UserConsent: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Cookie consent'), slug: cookieConsentUrl }], [cookieConsentUrl, t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('cookie consent', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Cookie consent update')}>
                <CookieConsentPage breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default UserConsent;
