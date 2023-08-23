import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'title'>;

type IconProps = NativeProps & {
    icon: string;
    alt: string | undefined;
    width?: number;
    height?: number;
};

export const Icon: FC<IconProps> = ({ icon, height, width, ...props }) => {
    return (
        <img
            src={`/icons/${icon}.png`}
            height={height !== undefined ? height : '24'}
            width={width !== undefined ? width : '24'}
            {...props}
        />
    );
};
