import { getCouldNotFindUserConsentPolicyArticleUrl } from 'app/_components/Blocks/UserConsent/userConsentUtils';
import { UserConsentContent } from 'app/_components/Page/UserConsent/UserConsentContent';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { notFound } from 'next/navigation';

export const dynamic = 'force-dynamic';

const UserConsentPage = async () => {
    const { data: settingsData, error: settingsError } = await getSettingsQuery();

    if (getCouldNotFindUserConsentPolicyArticleUrl(settingsData, settingsError)) {
        return notFound();
    }

    // TODO: add gtm
    //const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.user_consent, breadcrumbs);
    //useGtmPageViewEvent(gtmStaticPageViewEvent);

    return <UserConsentContent />;
};

export default UserConsentPage;
