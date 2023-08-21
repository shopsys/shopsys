import { twMergeCustom } from 'helpers/twMerge';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'title'>;

type IconProps = NativeProps &
    (
        | {
              icon: JSX.Element;
              alt?: never;
              width?: never;
              height?: never;
          }
        | {
              icon: string;
              alt: string | undefined;
              width?: number;
              height?: number;
          }
    );

export const Icon: FC<IconProps> = ({ icon, height, width, title, alt, className, ...props }) => (
    <>
        {typeof icon === 'string' ? (
            <img
                src={`/icons/${icon}.png`}
                height={height !== undefined ? height : '24'}
                width={width !== undefined ? width : '24'}
                title={title}
                alt={alt}
                data-testid={'basic-icon-' + icon}
            />
        ) : (
            <i
                className={twMergeCustom(
                    'inline-flex w-[14px] text-center font-normal normal-case leading-none [&>svg]:h-full [&>svg]:w-full',
                    className,
                )}
                data-testid={'basic-icon-iconsvg-' + icon}
                {...props}
            >
                {icon}
            </i>
        )}
    </>
);
