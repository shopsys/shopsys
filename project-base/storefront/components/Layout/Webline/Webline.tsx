import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    type?: WeblineType;
};

type WeblineType = 'colored' | 'dark' | 'light' | 'blog';

export const Webline: FC<WeblineProps> = ({ children, tid, type, className }) => {
    const content = (
        <div className={twMergeCustom('px-4 xl:mx-auto xl:w-full xl:max-w-7xl', className)} tid={tid}>
            {children}
        </div>
    );

    if (type) {
        return (
            <div
                className={twJoin(
                    type === 'colored' && 'bg-primaryDark',
                    type === 'dark' && 'bg-dark',
                    type === 'light' && 'bg-secondaryLight',
                    type === 'blog' && 'bg-[url("/images/blog-background.webp")] bg-cover bg-center bg-no-repeat',
                )}
            >
                {content}
            </div>
        );
    }

    return content;
};
