import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg';

/**
 *  Basic icon component unifies displaying icons
 */

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'onClick'>;

type IconProps =
    | {
          iconImage?: never;
          icon: IconName;
          title?: string;
      }
    | {
          icon?: never;
          iconImage: string;
          width?: number;
          height?: number;
          title?: string;
          alt: string;
      };

const Icon: FC<IconProps & NativeProps> = (props) => {
    if (props.iconImage !== undefined) {
        return (
            <img
                src={`/icons/${props.iconImage}.png`}
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
