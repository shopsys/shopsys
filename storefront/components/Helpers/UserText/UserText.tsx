import { GrapesJsStyled, UserTextStyled } from './UserText.style';

type UserTextProps = {
    htmlContent: string;
    isGrapesJs?: boolean;
};

export const UserText: FC<UserTextProps> = ({ dataTestId, htmlContent, isGrapesJs }) => {
    if (isGrapesJs) {
        return <GrapesJsStyled dangerouslySetInnerHTML={{ __html: htmlContent }} data-testid={dataTestId} />;
    }

    return <UserTextStyled dangerouslySetInnerHTML={{ __html: htmlContent }} data-testid={dataTestId} />;
};
