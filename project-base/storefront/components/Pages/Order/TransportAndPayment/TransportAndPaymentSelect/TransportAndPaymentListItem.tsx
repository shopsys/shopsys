import { twJoin } from 'tailwind-merge';

type TransportAndPaymentListItemProps = { isActive: boolean };

export const TransportAndPaymentListItem: FC<TransportAndPaymentListItemProps> = ({
    isActive,
    children,
    dataTestId,
}) => (
    <li
        className={twJoin(
            'relative order-1 flex min-w-full cursor-pointer flex-wrap border-b border-greyLighter p-3',
            isActive && 'border-b-0 bg-blueLight',
        )}
        data-testid={dataTestId}
    >
        {children}
    </li>
);
