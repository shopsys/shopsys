import { ReactNode } from 'react';
import { twMergeCustom } from 'helpers/twMerge';

type TableProps = {
    head?: ReactNode;
};

export const Table: FC<TableProps> = ({ head, children, className }) => (
    <div className={twMergeCustom('overflow-x-auto rounded-xl border-2 border-border p-6', className)}>
        <table className="w-full">
            {!!head && <thead className="border-b border-border">{head}</thead>}
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

export const Row: FC = ({ children, className, dataTestId }) => (
    <tr
        className={twMergeCustom('border-b border-border text-greyLight last:border-b-0', className)}
        data-testid={dataTestId}
    >
        {children}
    </tr>
);

export const Cell: FC<CellProps> = ({ align, isHead, isWithoutWrap, children, className, dataTestId, colSpan }) => {
    const Tag = isHead ? 'th' : 'td';

    return (
        <Tag
            colSpan={colSpan}
            className={twMergeCustom(
                'px-2 py-4 text-sm text-dark ',

                align === 'center' && 'text-center',
                align === 'right' && 'text-right',

                isWithoutWrap && 'whitespace-nowrap',
                className,
            )}
            data-testid={dataTestId}
        >
            {children}
        </Tag>
    );
};

export const CellHead: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell className={twMergeCustom('font-bold text-greyLight', className)} isHead {...props}>
        {children}
    </Cell>
);

export const CellMinor: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell className={twMergeCustom(' text-greyLight', className)} {...props}>
        {children}
    </Cell>
);
