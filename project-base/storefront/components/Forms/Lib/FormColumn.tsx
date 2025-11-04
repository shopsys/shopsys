import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    width?: string;
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ width, className, children }) => (
    <div
        className={twMergeCustom('vl:grid-cols-4 grid grid-cols-2 gap-5', className)}
        style={{
            ...(width !== undefined ? { width } : {}),
        }}
    >
        {children}
    </div>
);
