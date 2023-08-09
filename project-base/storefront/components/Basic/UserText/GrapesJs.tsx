import { twMergeCustom } from 'helpers/visual/twMerge';

type GrapesJsProps = {
    content?: string;
};

export const GrapesJs: FC<GrapesJsProps> = ({ dataTestId, content, className }) => (
    <section
        className={twMergeCustom('grapesjs-text user-text', className)}
        dangerouslySetInnerHTML={{ __html: content !== undefined ? content : '' }}
        data-testid={dataTestId}
        data-gjs-type="editable"
    />
);
