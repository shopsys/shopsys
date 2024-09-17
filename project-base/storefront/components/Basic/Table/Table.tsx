import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type TableProps = {
    head?: ReactNode;
    tableClassName?: string;
};

export const Table: FC<TableProps> = ({ head, children, className, tableClassName }) => (
    <div className={twMergeCustom('overflow-x-auto', className)}>
        <table className={twMergeCustom('w-full', tableClassName)}>
            {!!head && <thead>{head}</thead>}
            <tbody>{children}</tbody>
        </table>
    </div>
);

type CellProps = {
    align?: 'center' | 'right';
    isWithoutWrap?: boolean;
    isHead?: boolean;
    colSpan?: number;
};

export const Row: FC = ({ children, className }) => (
    <tr className={twMergeCustom('bg-tableBackground text-tableText odd:bg-tableBackgroundContrast', className)}>
        {children}
    </tr>
);

export const Cell: FC<CellProps> = ({ align, isHead, isWithoutWrap, children, className, colSpan }) => {
    const Tag = isHead ? 'th' : 'td';

    return (
        <Tag
            colSpan={colSpan}
            className={twMergeCustom(
                'px-2 py-4 text-sm',

                align === 'center' && 'text-center',
                align === 'right' && 'text-right',

                isWithoutWrap && 'whitespace-nowrap',
                className,
            )}
        >
            {children}
        </Tag>
    );
};

export const CellHead: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell isHead className={twMergeCustom(className, 'bg-tableBackgroundHeader text-tableTextHeader')} {...props}>
        {children}
    </Cell>
);

export const CellMinor: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell className={className} {...props}>
        {children}
    </Cell>
);
