import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    width?: string;
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ width, className, children }) => (
    <div
        className={twMergeCustom('grid grid-cols-2 vl:grid-cols-4 gap-5', className)}
        style={{
            ...(width !== undefined ? { width } : {}),
        }}
    >
        {children}
    </div>
);
