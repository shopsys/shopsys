import { GrapesJs } from './GrapesJs';

type UserTextProps = {
    htmlContent: string;
    isGrapesJs?: boolean;
};

export const UserText: FC<UserTextProps> = ({ dataTestId, htmlContent, isGrapesJs }) => {
    if (isGrapesJs) {
        return <GrapesJs content={htmlContent} dataTestId={dataTestId} />;
    }

    return <section dangerouslySetInnerHTML={{ __html: htmlContent }} data-testid={dataTestId} />;
};
