import { CountBadge } from 'components/Basic/CountBadge/CountBadge';

export const CartCount: FC = ({ children }) => (
    <CountBadge className="absolute -top-1.5 vl:top-[-6.5px] -right-3 vl:-right-3 bg-background-warning text-text-default">
        {children}
    </CountBadge>
);
