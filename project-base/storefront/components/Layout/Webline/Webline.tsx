import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
    width?: 'md' | 'lg' | 'xl' | 'xxl';
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className, width = 'xl' }) => {
    const widthClasses = {
        md: 'vl:max-w-[840px]',
        lg: 'vl:max-w-screen-lg',
        xl: 'vl:max-w-screen-xl',
        xxl: 'vl:max-w-[1400px]',
    };

    const weblineClassName = twJoin('px-5 vl:mx-auto vl:w-full', widthClasses[width]);

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
