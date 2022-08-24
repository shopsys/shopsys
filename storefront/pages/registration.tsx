import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { RegistrationContent } from 'components/Pages/Registration/RegistrationContent';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const RegistrationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Registration'), slug: registrationUrl }], [registrationUrl, t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Registration')}>
                <RegistrationContent breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default RegistrationPage;
