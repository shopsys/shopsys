import { twMergeCustom } from 'helpers/twMerge';
import { twJoin } from 'tailwind-merge';

type WeblineProps = {
    type?: WeblineType;
};

type WeblineType = 'colored' | 'dark' | 'light' | 'blog';

const getDataTestId = (dataTestId?: string, type?: WeblineType) =>
    dataTestId ?? 'layout-webline' + (type ? '-' + type : '');

export const Webline: FC<WeblineProps> = ({ children, dataTestId, type, className }) => {
    const content = (
        <div className={twMergeCustom('px-4 xl:mx-auto xl:w-full xl:max-w-7xl', className)}>{children}</div>
    );

    if (type) {
        return (
            <div
                data-testid={getDataTestId(dataTestId, type)}
                className={twJoin(
                    type === 'colored' && 'bg-primary',
                    type === 'dark' && 'bg-greyDark',
                    type === 'light' && 'bg-orangeLight',
                    type === 'blog' && 'bg-[url("/images/blog-background.webp")] bg-cover bg-center bg-no-repeat',
                )}
            >
                {content}
            </div>
        );
    }

    return content;
};
