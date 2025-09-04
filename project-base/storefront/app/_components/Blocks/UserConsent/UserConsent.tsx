'use client';

import { UserConsentForm } from './UserConsentForm';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const UserConsent = ({ isConsentUpdatePage }: { isConsentUpdatePage: boolean }) => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);

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
            <div className="border-borderAccent bg-background-more absolute right-4 bottom-3 w-[calc(100vw-32px)] max-w-lg rounded-sm border-4 p-5 shadow-md">
                <UserConsentForm onSetCallback={onSetCallback} />
            </div>
        </div>
    );
};
