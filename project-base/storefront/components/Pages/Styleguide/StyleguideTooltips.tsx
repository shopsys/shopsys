import { StyleguideSection } from './StyleguideElements';
import { Placement } from '@floating-ui/react';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import React from 'react';

export const StyleguideTooltips: FC = () => {
    return (
        <StyleguideSection className="flex flex-wrap gap-3" title="Tooltips">
            <TooltipBox label="Top">ℹ️ Top tooltip</TooltipBox>

            <TooltipBox label="Right" placement="right">
                ℹ️ Right tooltip
            </TooltipBox>

            <TooltipBox label="Bottom" placement="bottom">
                ℹ️ Bottom tooltip
            </TooltipBox>

            <TooltipBox label="Left" placement="left">
                ℹ️ Left tooltip
            </TooltipBox>
        </StyleguideSection>
    );
};

const TooltipBox: FC<{ label: string; placement?: Placement }> = ({ label, placement, children }) => (
    <Tooltip label={label} placement={placement}>
        <div className="p-5 bg-skyBlue cursor-pointer">{children}</div>
    </Tooltip>
);
