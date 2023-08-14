import { IconName, IconsSvgMap } from './IconsSvgMap';
import { HTMLAttributes } from 'react';
import { twMergeCustom } from 'helpers/twMerge';

type IconSvgProps = HTMLAttributes<HTMLElement> & {
    icon: IconName;
    width?: number;
    height?: number;
};

export const IconSvg: FC<IconSvgProps> = ({ icon, className, ...props }) => (
    <i
        className={twMergeCustom(
            'inline-flex w-[14px] text-center font-normal normal-case leading-none [&>svg]:h-full [&>svg]:w-full',
            className,
        )}
        data-testid={'basic-icon-iconsvg-' + icon}
        {...props}
    >
        {IconsSvgMap[icon]}
    </i>
);
