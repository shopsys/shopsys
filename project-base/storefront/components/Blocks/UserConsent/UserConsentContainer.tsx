import { UserConsentForm } from './UserConsentForm';
import { useCallback, useState } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';

const TEST_IDENTIFIER = 'blocks-userconsent';

export const UserConsentContainer: FC = () => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);

    const onSetCallback = useCallback(() => {
        if (userConsent) {
            setUserConsentVisibility(false);
        }
    }, [userConsent]);

    if (!isUserConsentVisible) {
        return null;
    }

    return (
        <div className="fixed left-0 bottom-0 z-maximum flex w-full justify-end">
            <div
                className="absolute right-4 bottom-3 w-[calc(100vw-32px)] max-w-lg rounded-xl border-4 border-primaryLight bg-creamWhite p-5 shadow-md"
                data-testid={TEST_IDENTIFIER}
            >
                <UserConsentForm onSetCallback={onSetCallback} />
            </div>
        </div>
    );
};
