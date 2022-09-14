import { UserConsentForm } from '../UserConsentForm';
import { UserConsentContainerStyled, UserConsentStyled } from './UserConsentContainer.style';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { FC, useCallback, useState } from 'react';

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
        <UserConsentContainerStyled>
            <UserConsentStyled data-testid={TEST_IDENTIFIER}>
                <UserConsentForm onSetCallback={onSetCallback} />
            </UserConsentStyled>
        </UserConsentContainerStyled>
    );
};
