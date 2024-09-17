import { StyleguideSection } from './StyleguideElements';
import { Placement } from '@floating-ui/react';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import React from 'react';

export const StyleguideTooltips: FC = () => {
    return (
        <StyleguideSection className="flex flex-wrap gap-3" title="Tooltips">
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
        </StyleguideSection>
    );
};

const TooltipBox: FC<{ label: string; placement?: Placement }> = ({ label, placement, children }) => (
    <Tooltip label={label} placement={placement}>
        <div className="rounded-full bg-backgroundBrandLess px-2 py-1 text-textInverted">{children}</div>
    </Tooltip>
);
