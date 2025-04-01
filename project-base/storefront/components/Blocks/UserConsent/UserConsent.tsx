import { UserConsentForm } from './UserConsentForm';
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
        <div className="z-maximum fixed bottom-0 left-0 flex w-full justify-end">
            <div className="border-border-default bg-backgroundMore absolute right-4 bottom-3 w-[calc(100vw-32px)] max-w-lg rounded-sm border-4 p-5 shadow-md">
                <UserConsentForm onSetCallback={onSetCallback} />
            </div>
        </div>
    );
};
