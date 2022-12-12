import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CookieConsentContent } from 'components/Pages/CookieConsent/CookieConsentContent';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const CookieConsentPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Cookie consent'), slug: cookieConsentUrl }], [cookieConsentUrl, t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('cookie consent', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Cookie consent update')}>
                <CookieConsentContent breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient((redisClient) => async (context) => {
        initDomainConfig(context, store);

        return initServerSideProps({ context, store, redisClient });
    }),
);

export default CookieConsentPage;
