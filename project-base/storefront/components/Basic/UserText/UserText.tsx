import { GrapesJs } from './GrapesJs';

type UserTextProps = {
    htmlContent: string;
    isGrapesJs?: boolean;
};

export const UserText: FC<UserTextProps> = ({ htmlContent, isGrapesJs }) => {
    if (isGrapesJs) {
        return <GrapesJs content={htmlContent} />;
    }

    return <section className="user-text" dangerouslySetInnerHTML={{ __html: htmlContent }} />;
};
