import { UserConsentContainerStyled, UserConsentStyled } from './UserConsentContainer.style';
import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { FC, useState } from 'react';

export const UserConsentContainer: FC = () => {
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    if (!isUserConsentVisible) {
        return null;
    }

    return (
        <UserConsentContainerStyled>
            <UserConsentStyled>
                <UserConsentForm onSetUserConsentVisibilityCallback={setUserConsentVisibility} />
            </UserConsentStyled>
        </UserConsentContainerStyled>
    );
};
