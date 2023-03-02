import { IconName } from './IconsSvgMap';
import { IconSvg } from './IconSvg';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'title'>;

type IconBaseProps = {
    width?: number;
    height?: number;
};

type IconProps = NativeProps &
    IconBaseProps &
    (
        | {
              iconType: 'icon';
              icon: IconName;
              alt?: never;
          }
        | {
              iconType: 'image';
              icon: string;
              alt: string;
          }
    );

export const Icon: FC<IconProps> = ({ icon, iconType, height, width, title, alt, ...props }) => (
    <>
        {iconType === 'icon' ? (
            <IconSvg icon={icon} width={width} height={height} {...props} />
        ) : (
            <img
                src={`/icons/${icon}.png`}
                height={height !== undefined ? height : '24'}
                width={width !== undefined ? width : '24'}
                title={title}
                alt={alt}
                data-testid={'basic-icon-' + icon}
            />
        )}
    </>
);
