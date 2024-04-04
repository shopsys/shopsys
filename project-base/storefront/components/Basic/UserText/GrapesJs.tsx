import { twMergeCustom } from 'utils/twMerge';

type GrapesJsProps = {
    content?: string;
};

export const GrapesJs: FC<GrapesJsProps> = ({ content, className }) => (
    <section
        className={twMergeCustom('user-text', className)}
        dangerouslySetInnerHTML={{ __html: content || '' }}
        data-gjs-type="editable"
    />
);
