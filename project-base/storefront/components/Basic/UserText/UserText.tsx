import { GrapesJs } from './GrapesJs';
import styles from './UserText.module.sass';

type UserTextProps = {
    htmlContent: string;
    isGrapesJs?: boolean;
};

export const UserText: FC<UserTextProps> = ({ dataTestId, htmlContent, isGrapesJs }) => {
    if (isGrapesJs) {
        return <GrapesJs content={htmlContent} dataTestId={dataTestId} />;
    }

    return (
        <section
            className={styles.userTextBasic}
            dangerouslySetInnerHTML={{ __html: htmlContent }}
            data-testid={dataTestId}
        />
    );
};
