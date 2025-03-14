import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className }) => {
    const weblineClassName = 'px-5 xl:mx-auto xl:w-full xl:max-w-screen-xl';

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
