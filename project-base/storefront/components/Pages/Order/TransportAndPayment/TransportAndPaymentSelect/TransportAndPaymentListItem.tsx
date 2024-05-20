import { twMergeCustom } from 'utils/twMerge';

type TransportAndPaymentListItemProps = { isActive: boolean };

export const TransportAndPaymentListItem: FC<TransportAndPaymentListItemProps> = ({
    isActive,
    children,
    className,
}) => (
    <li
        className={twMergeCustom(
            'relative order-1 flex min-w-full cursor-pointer flex-wrap gap-2 border-b border-graySlate p-3',
            isActive && 'border-b-0 bg-grayLight',
            className,
        )}
    >
        {children}
    </li>
);
