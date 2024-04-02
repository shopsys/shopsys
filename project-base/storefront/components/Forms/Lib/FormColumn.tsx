import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    width?: string;
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ width, className, children }) => (
    <div
        className={twMergeCustom('-ml-3 flex flex-wrap [&>.form-line]:pl-3', className)}
        style={{
            ...(width !== undefined ? { width } : {}),
        }}
    >
        {children}
    </div>
);
