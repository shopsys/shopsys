import { twMergeCustom } from 'helpers/twMerge';

type GrapesJsProps = {
    content?: string;
};

export const GrapesJs: FC<GrapesJsProps> = ({ dataTestId, content, className }) => (
    <section
        className={twMergeCustom('grapesjs-text user-text', className)}
        dangerouslySetInnerHTML={{ __html: content !== undefined ? content : '' }}
        data-gjs-type="editable"
        data-testid={dataTestId}
    />
);
