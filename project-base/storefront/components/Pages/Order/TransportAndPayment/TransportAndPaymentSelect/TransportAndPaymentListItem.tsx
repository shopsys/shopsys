import { twMergeCustom } from 'utils/twMerge';

type TransportAndPaymentListItemProps = { isActive: boolean };

export const TransportAndPaymentListItem: FC<TransportAndPaymentListItemProps> = ({
    isActive,
    children,
    className,
}) => (
    <li
        className={twMergeCustom(
            'border-borderAccent relative order-1 flex min-w-full cursor-pointer flex-wrap gap-2 border-b p-4 transition last:border-b-0',
            isActive && 'bg-backgroundMost',
            className,
        )}
    >
        {children}
    </li>
);
