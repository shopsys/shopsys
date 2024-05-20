import { UserConsentForm } from './UserConsentForm';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const UserConsent: FC<{ url: string }> = ({ url }) => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);
    const router = useRouter();
    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
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
        <div className="fixed left-0 bottom-0 z-maximum flex w-full justify-end">
            <div className="absolute right-4 bottom-3 w-[calc(100vw-32px)] max-w-lg rounded border-4 border-primary bg-whiteSnow p-5 shadow-md">
                <UserConsentForm onSetCallback={onSetCallback} />
            </div>
        </div>
    );
};
