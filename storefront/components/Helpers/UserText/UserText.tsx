import { GrapesJsStyled, UserTextStyled } from './UserText.style';
import { FC } from 'react';

type UserTextProps = {
    htmlContent: string;
    testIdentifier?: string;
    isGrapesJs?: boolean;
};

export const UserText: FC<UserTextProps> = ({ testIdentifier, htmlContent, isGrapesJs }) => {
    if (isGrapesJs) {
        return (
            <GrapesJsStyled
                dangerouslySetInnerHTML={{ __html: htmlContent }}
                data-testid={testIdentifier}
            ></GrapesJsStyled>
        );
    }

    return <UserTextStyled dangerouslySetInnerHTML={{ __html: htmlContent }} data-testid={testIdentifier} />;
};
