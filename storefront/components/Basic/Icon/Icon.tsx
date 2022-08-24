import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg/IconSvg';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'onClick'>;

type IconProps = NativeProps & { iconType: 'icon' | 'image'; title?: string } & (
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
    );

export const Icon: FC<IconProps> = (props) => {
    if (props.iconType === 'image') {
        const testIdentifier = 'basic-icon-' + props.icon;

        return (
            <img
                src={`/icons/${props.icon}.png`}
                height={props.height !== undefined ? props.height : '24'}
                width={props.width !== undefined ? props.width : '24'}
                title={props.title}
                alt={props.alt}
                data-testid={testIdentifier}
            />
        );
    }

    return <IconSvg {...props} icon={props.icon} />;
};
