import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg/IconSvg';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'title' | 'onClick' | 'className'>;

type IconProps = NativeProps &
    (
        | {
              iconType: 'icon';
              icon: IconName;
              width?: never;
              height?: never;
              alt?: never;
          }
        | {
              iconType: 'image';
              icon: string;
              width?: number;
              height?: number;
              alt: string;
          }
    );

const getTestIdentifier = (icon: string) => 'basic-icon-' + icon;

export const Icon: FC<IconProps> = ({ icon, iconType, title, alt, height, width, onClick, className }) => {
    if (iconType === 'icon') {
        return <IconSvg className={className} icon={icon as IconName} onClick={onClick} />;
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
