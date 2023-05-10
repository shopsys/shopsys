import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type WeblineProps = NativeProps & {
    type?: WeblineType;
};

type WeblineType = 'colored' | 'dark' | 'light' | 'blog';

const getDataTestId = (dataTestId?: string, type?: WeblineType) =>
    dataTestId ?? 'layout-webline' + (type ? '-' + type : '');

export const Webline: FC<WeblineProps> = ({ children, style, dataTestId, type, className }) => (
    <div
        className={twMergeCustom(
            type === 'colored' && 'bg-primary',
            type === 'dark' && 'bg-greyDark',
            type === 'light' && 'bg-orangeLight',
            type === 'blog' && 'bg-[url("/images/blog-background.png")] bg-cover bg-center bg-no-repeat',
            className,
        )}
        style={style}
        data-testid={getDataTestId(dataTestId, type)}
    >
        <div className="px-5 xl:mx-auto xl:w-full xl:max-w-7xl">{children}</div>
    </div>
);
