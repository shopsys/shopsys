import { FC } from 'react';
import { UserTextStyled } from './UserText.style';

/**
 * Global component that serves as a wrapper
 * for rendering the text/HTML users can insert into the WYSIWYG editor
 */
type UserTextProps = {
    /**
     * The actual content of the wrapper element,
     * can be both plain text and HTML content
     */
    htmlContent: string;
    'data-testid'?: string;
};

export const UserText: FC<UserTextProps> = ({ ...props }) => {
    const attr = {
        'data-testid': props['data-testid'],
    };

    return <UserTextStyled dangerouslySetInnerHTML={{ __html: props.htmlContent }} {...attr}></UserTextStyled>;
};

/* @component */
export default UserText;
