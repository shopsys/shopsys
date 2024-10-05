import { twMergeCustom } from 'utils/twMerge';

type WeblineProps = {
    wrapperClassName?: string;
};

export const Webline: FC<WeblineProps> = ({ children, tid, wrapperClassName, className }) => {
    const content = (
        <div className={twMergeCustom('px-5 xl:mx-auto xl:w-full xl:max-w-[1240px]', className)} tid={tid}>
            {children}
        </div>
    );

    return <div className={wrapperClassName}>{content}</div>;
};
