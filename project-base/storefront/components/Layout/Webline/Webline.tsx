import { twMergeCustom } from 'helpers/twMerge';

type WeblineProps = {
    type?: WeblineType;
};

type WeblineType = 'colored' | 'dark' | 'light' | 'blog';

const getDataTestId = (dataTestId?: string, type?: WeblineType) =>
    dataTestId ?? 'layout-webline' + (type ? '-' + type : '');

export const Webline: FC<WeblineProps> = ({ children, dataTestId, type, className }) => (
    <div
        className={twMergeCustom(
            type === 'colored' && 'bg-primary',
            type === 'dark' && 'bg-greyDark',
            type === 'light' && 'bg-orangeLight',
            type === 'blog' && 'bg-[url("/images/blog-background.webp")] bg-cover bg-center bg-no-repeat',
            className,
        )}
        data-testid={getDataTestId(dataTestId, type)}
    >
        <div className="px-4 xl:mx-auto xl:w-full xl:max-w-7xl">{children}</div>
    </div>
);
