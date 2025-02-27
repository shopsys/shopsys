import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
    width?: 'lg' | 'xl' | 'xxl';
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className, width = 'xl' }) => {
    const widthClasses = {
        lg: 'xl:max-w-screen-lg',
        xl: 'xl:max-w-screen-xl',
        xxl: 'xl:max-w-[1400px]',
    };

    const weblineClassName = twJoin('px-5 xl:mx-auto xl:w-full', widthClasses[width]);

    if (!wrapperClassName) {
        return (
            <section className={twMergeCustom(weblineClassName, className)} tid={tid}>
                {children}
            </section>
        );
    }

    return (
        <section className={wrapperClassName}>
            <div className={twMergeCustom(weblineClassName, className)} tid={tid}>
                {children}
            </div>
        </section>
    );
};
