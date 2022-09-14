import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataOverviewContent } from 'components/Pages/PersonalData/Overview/PersonalDataOverviewContent';
import { PersonalDataPageTextQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const PersonalDataOverviewPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [personalDataOverviewUrl] = getInternationalizedStaticUrls(['/personal-data-overview'], domainUrl);
    const breadcrumbs = useMemo(
        () => [{ name: t('Personal Data Overview'), slug: personalDataOverviewUrl }],
        [personalDataOverviewUrl, t],
    );
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Personal Data Overview')}>
                <PersonalDataOverviewContent breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, false, [{ query: PersonalDataPageTextQueryDocumentApi }]);
});

export default PersonalDataOverviewPage;
