import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { NewPasswordContent } from 'components/Pages/NewPassword/NewPasswordContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const NewPasswordPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [newPasswordUrl] = getInternationalizedStaticUrls(['/new-password'], url);
    const breadcrumbs: BreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Set new password'), slug: newPasswordUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const router = useRouter();
    const { hash, email } = router.query;

    let hashParam = '';
    if (hash !== undefined) {
        if (Array.isArray(hash)) {
            hashParam = hash[0];
        } else if (hash.trim() !== '') {
            hashParam = hash.trim();
        }
    }

    let emailParam = '';
    if (email !== undefined) {
        if (Array.isArray(email)) {
            emailParam = email[0];
        } else if (email.trim() !== '') {
            emailParam = email.trim();
        }
    }

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout
                title={t('Set new password')}
                breadcrumbs={
                    hash === '' || email === ''
                        ? breadcrumbs
                        : [{ __typename: 'Link', name: t('Set new password'), slug: newPasswordUrl }]
                }
            >
                <NewPasswordContent email={emailParam} hash={hashParam} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default NewPasswordPage;
