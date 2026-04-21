import { twMergeCustom } from 'utils/twMerge';

type FormColumnProps = {
    className?: string;
};

export const FormColumn: FC<FormColumnProps> = ({ className, children }) => (
    <div className={twMergeCustom('grid grid-cols-12 gap-5', className)}>{children}</div>
);
