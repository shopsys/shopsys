import { UserConsentForm } from '../UserConsentForm';
import { UserConsentContainerStyled, UserConsentStyled } from './UserConsentContainer.style';
import { FC, useState } from 'react';

const TEST_IDENTIFIER = 'blocks-userconsent';

export const UserConsentContainer: FC = () => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);

    if (!isUserConsentVisible) {
        return null;
    }

    return (
        <UserConsentContainerStyled>
            <UserConsentStyled data-testid={TEST_IDENTIFIER}>
                <UserConsentForm onSetUserConsentVisibilityCallback={setUserConsentVisibility} />
            </UserConsentStyled>
        </UserConsentContainerStyled>
    );
};
