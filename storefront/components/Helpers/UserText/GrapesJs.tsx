import styles from './UserText.module.sass';
import { twMergeCustom } from 'utils/twMerge';

type GrapesJsProps = {
    dataGjsType?: string;
    content?: string;
};

export const GrapesJs: FC<GrapesJsProps> = ({ dataTestId, content, className }) => (
    <section
        className={twMergeCustom(styles.grapesJs, styles.userTextBasic, className)}
        dangerouslySetInnerHTML={{ __html: content !== undefined ? content : '' }}
        data-testid={dataTestId}
    />
);
