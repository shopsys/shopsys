import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
    width?: 'sm' | 'lg' | 'vl' | 'xl' | 'xxl';
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className, width = 'xxl' }) => {
    const widthClasses = {
        sm: 'vl:max-w-screen-sm',
        lg: 'vl:max-w-screen-lg',
        vl: 'vl:max-w-210',
        xl: 'vl:max-w-screen-xl',
        xxl: 'vl:max-w-default-max-width',
    };

    const weblineClassName = twJoin('vl:mx-auto vl:w-full px-5', widthClasses[width]);

    if (!wrapperClassName) {
        return (
            <section className={twMergeCustom(weblineClassName, className)} data-tid={tid}>
                {children}
            </section>
        );
    }

    return (
        <section className={wrapperClassName}>
            <div className={twMergeCustom(weblineClassName, className)} data-tid={tid}>
                {children}
            </div>
        </section>
    );
};
