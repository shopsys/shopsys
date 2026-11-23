import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { StyleguidePreview } from './StyleguideElements';

export const StyleguideTooltips: FC = () => {
    return (
        <StyleguidePreview className="flex flex-wrap gap-3">
            <TooltipBox label="Top">Top tooltip</TooltipBox>

            <TooltipBox label="Right" placement="right">
                Right tooltip
            </TooltipBox>

            <TooltipBox label="Bottom" placement="bottom">
                Bottom tooltip
            </TooltipBox>

            <TooltipBox label="Left" placement="left">
                Left tooltip
            </TooltipBox>
        </StyleguidePreview>
    );
};

const TooltipBox: FC<{ label: string; placement?: 'top' | 'bottom' | 'left' | 'right' }> = ({
    label,
    placement,
    children,
}) => (
    <Tooltip label={label} placement={placement}>
        <div className="rounded-full bg-background-brand-less px-2 py-1 text-text-inverted">{children}</div>
    </Tooltip>
);
