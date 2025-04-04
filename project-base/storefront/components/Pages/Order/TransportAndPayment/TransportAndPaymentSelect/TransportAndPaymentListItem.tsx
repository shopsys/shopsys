import { twMergeCustom } from 'utils/twMerge';

export const TransportAndPaymentListItem: FC = ({ children, className }) => (
    <li
        className={twMergeCustom(
            'border-borderAccentLess relative order-1 flex min-w-full cursor-pointer flex-wrap gap-2 border-b py-4 transition last:border-b-0',
            className,
        )}
    >
        {children}
    </li>
);
