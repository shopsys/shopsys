import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type WeblineProps = NativeProps & {
    type?: WeblineType;
    testIdentifier?: string;
};

type WeblineType = 'colored' | 'dark' | 'light' | 'blog';

const getTestIdentifier = (testIdentifier?: string, type?: WeblineType) =>
    testIdentifier ?? 'layout-webline' + (type ? '-' + type : '');

export const Webline: FC<WeblineProps> = ({ children, style, testIdentifier, type, className }) => (
    <div
        className={twMergeCustom(
            type === 'colored' && 'bg-primary',
            type === 'dark' && 'bg-greyDark',
            type === 'light' && 'bg-orangeLight',
            type === 'blog' && 'bg-[url("/images/blog-background.png")] bg-cover bg-center bg-no-repeat',
            className,
        )}
        style={style}
        data-testid={getTestIdentifier(testIdentifier, type)}
    >
        <div className="px-5 xl:mx-auto xl:w-full xl:max-w-7xl">{children}</div>
    </div>
);
