import { getSettingsQuery } from './_queries/getSettingsQuery';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { SkeletonPageHome } from 'components/Blocks/Skeleton/SkeletonPageHome';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';
import TransServer from 'utils/translation/TransServer';

export default async function HomePage() {
    const settingsData = await getSettingsQuery();
    const termsAndConditionsArticleUrl = settingsData?.settings?.termsAndConditionsArticleUrl;
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    return (
        <Webline>
            <Suspense fallback={<SkeletonPageHome />}>
                <h1>HOME PAGE</h1>
                <p>This is the home page.</p>
                <p>Testing {`<Trans ... />`} component </p>
                <TransServer
                    defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                    i18nKey="ContactInformationInfo"
                    components={{
                        lnk1: termsAndConditionsArticleUrl ? (
                            <Link isExternal href={termsAndConditionsArticleUrl} target="_blank" />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                        lnk2: privacyPolicyArticleUrl ? (
                            <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            </Suspense>
        </Webline>
    );
}
