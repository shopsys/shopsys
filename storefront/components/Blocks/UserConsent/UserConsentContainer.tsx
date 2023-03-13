import { UserConsentForm } from './UserConsentForm';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { useCallback, useState } from 'react';

const TEST_IDENTIFIER = 'blocks-userconsent';

export const UserConsentContainer: FC = () => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);

    const onSetCallback = useCallback(() => {
        if (getUserConsentCookie() !== null) {
            setUserConsentVisibility(false);
        }
    }, []);

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
