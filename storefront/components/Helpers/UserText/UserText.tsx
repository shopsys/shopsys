import { UserTextStyled } from './UserText.style';
import { FC } from 'react';

type UserTextProps = {
    htmlContent: string;
    testIdentifier?: string;
};

export const UserText: FC<UserTextProps> = ({ testIdentifier, htmlContent }) => (
    <UserTextStyled dangerouslySetInnerHTML={{ __html: htmlContent }} data-testid={testIdentifier} />
);
