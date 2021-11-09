import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg';

/**
 *  Basic icon component unifies displaying icons
 */

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'onClick'>;

type IconProps = { iconType: 'icon' | 'image'; title?: string } & (
    | {
          iconType: 'icon';
          icon: IconName;
      }
    | {
          iconType: 'image';
          icon: string;
          width?: number;
          height?: number;
          alt: string;
      }
) &
    NativeProps;

const Icon: FC<IconProps> = (props) => {
    if (props.iconType === 'image') {
        return (
            <img
                src={`/icons/${props.icon}.png`}
                height={props.height !== undefined ? props.height : '24'}
                width={props.width !== undefined ? props.width : '24'}
                title={props.title}
                alt={props.alt}
            />
        );
    }

    return <IconSvg {...props} icon={props.icon} />;
};

export default Icon;
