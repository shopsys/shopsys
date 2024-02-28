import { UserConsentForm } from './UserConsentForm';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const UserConsent: FC = () => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);

    const onSetCallback = () => {
        if (userConsent) {
            setUserConsentVisibility(false);
        }
    };

    if (!isUserConsentVisible) {
        return null;
    }

    return (
        <div className="fixed left-0 bottom-0 z-maximum flex w-full justify-end">
            <div className="absolute right-4 bottom-3 w-[calc(100vw-32px)] max-w-lg rounded border-4 border-primaryLight bg-creamWhite p-5 shadow-md">
                <UserConsentForm onSetCallback={onSetCallback} />
            </div>
        </div>
    );
};
