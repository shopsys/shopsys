import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    width?: string;
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ width, className, children }) => (
    <div
        className={twMergeCustom('flex flex-col vl:flex-row vl:flex-wrap vl:gap-3', className)}
        style={{
            ...(width !== undefined ? { width } : {}),
        }}
    >
        {children}
    </div>
);
