import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg/IconSvg';
import { CSSProperties, FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'title' | 'onClick' | 'className'>;

type IconProps = NativeProps & { width?: number; height?: number } & (
        | {
              iconType: 'icon';
              icon: IconName;
              alt?: never;
              color?: CSSProperties['color'];
          }
        | {
              iconType: 'image';
              icon: string;
              alt: string;
              color?: never;
          }
    );

const getTestIdentifier = (icon: string) => 'basic-icon-' + icon;

export const Icon: FC<IconProps> = ({ icon, iconType, title, alt, height, width, onClick, className, color }) => {
    if (iconType === 'icon') {
        return (
            <IconSvg
                className={className}
                icon={icon as IconName}
                onClick={onClick}
                color={color}
                height={height}
                width={width}
            />
        );
    }

    return (
        <img
            className={className}
            src={`/icons/${icon}.png`}
            height={height !== undefined ? height : '24'}
            width={width !== undefined ? width : '24'}
            title={title}
            alt={alt}
            onClick={onClick}
            data-testid={getTestIdentifier(icon)}
        />
    );
};
