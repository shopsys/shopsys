import { UserConsentForm } from './UserConsentForm';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const UserConsent: FC<{ url: string }> = ({ url }) => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);
    const router = useRouter();
    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/user-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    const onSetCallback = () => {
        if (userConsent) {
            setUserConsentVisibility(false);
        }
    };

    if (userConsent || isConsentUpdatePage || !isUserConsentVisible) {
        return null;
    }

    return (
        <div className="z-maximum bg-background-default border-border-default fixed right-5 bottom-5 rounded-xl border-5 p-4">
            <VerticalStack gap="sm">
                <UserConsentForm onSetCallback={onSetCallback} />
            </VerticalStack>
        </div>
    );
};
