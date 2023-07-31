import { CommonLayout } from 'components/Layout/CommonLayout';
import { RegistrationContent } from 'components/Pages/Registration/RegistrationContent';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const RegistrationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Registration'), slug: registrationUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Registration')}>
            <RegistrationContent breadcrumbs={breadcrumbs} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default RegistrationPage;
