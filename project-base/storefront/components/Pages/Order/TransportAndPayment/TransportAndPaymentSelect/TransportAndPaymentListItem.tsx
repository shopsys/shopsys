import { twMergeCustom } from 'helpers/twMerge';

type TransportAndPaymentListItemProps = { isActive: boolean };

export const TransportAndPaymentListItem: FC<TransportAndPaymentListItemProps> = ({
    isActive,
    children,
    className,
}) => (
    <li
        className={twMergeCustom(
            'relative order-1 flex min-w-full cursor-pointer flex-wrap gap-2 border-b border-greyLighter p-3',
            isActive && 'border-b-0 bg-blueLight',
            className,
        )}
    >
        {children}
    </li>
);
