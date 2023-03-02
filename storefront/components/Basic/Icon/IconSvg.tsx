import { IconName, IconsSvgMap } from './IconsSvgMap';
import { HTMLAttributes } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type IconSvgProps = HTMLAttributes<HTMLElement> & {
    icon: IconName;
    width?: number;
    height?: number;
};

export const IconSvg: FC<IconSvgProps> = ({ icon, height = '14px', width = '14px', className, ...props }) => (
    <i
        className={twMergeCustom('inline-flex text-center font-normal normal-case leading-none', className)}
        style={{
            width,
            height,
        }}
        data-testid={'basic-icon-iconsvg-' + icon}
        {...props}
    >
        {IconsSvgMap[icon]}
    </i>
);
