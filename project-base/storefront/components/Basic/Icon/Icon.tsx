import { IconSvg } from './IconSvg';
import { IconName } from './IconsSvgMap';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'title'>;

type IconProps = NativeProps &
    (
        | {
              iconType: 'icon';
              icon: IconName;
              alt?: never;
              width?: never;
              height?: never;
          }
        | {
              iconType: 'image';
              icon: string;
              alt: string;
              width?: number;
              height?: number;
          }
    );

export const Icon: FC<IconProps> = ({ icon, iconType, height, width, title, alt, ...props }) => (
    <>
        {iconType === 'icon' ? (
            <IconSvg icon={icon} {...props} />
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
