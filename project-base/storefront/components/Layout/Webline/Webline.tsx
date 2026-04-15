import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
    width?: 'lg' | 'vl' | 'xl' | 'xxl';
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className, width = 'xl' }) => {
    const widthClasses = {
        lg: 'vl:max-w-screen-lg',
        vl: 'vl:max-w-[840px]',
        xl: 'vl:max-w-screen-xl',
        xxl: 'vl:max-w-[1400px]',
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
