import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

export const CookieConsentPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], domainUrl);

    return (
        <SimpleLayout
            heading={t('Cookie consent')}
            breadcrumb={[{ name: t('Cookie consent'), slug: cookieConsentUrl }]}
        >
            <UserConsentForm />
        </SimpleLayout>
    );
};
