import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    width?: string;
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ width, className, children }) => (
    <div
        style={{
            ...(width !== undefined ? { width } : {}),
        }}
        className={twMergeCustom('-ml-3 flex flex-wrap [&>[data-testid="form-line"]]:pl-3', className)}
    >
        {children}
    </div>
);
