import { UserTextStyled } from './UserText.style';
import { FC } from 'react';

type UserTextProps = {
    htmlContent: string;
    'data-testid'?: string;
};

export const UserText: FC<UserTextProps> = ({ ...props }) => {
    const attr = {
        'data-testid': props['data-testid'],
    };

    return <UserTextStyled dangerouslySetInnerHTML={{ __html: props.htmlContent }} {...attr}></UserTextStyled>;
};

export default UserText;
