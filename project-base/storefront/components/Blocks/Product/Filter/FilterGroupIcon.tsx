import { Icon } from 'components/Basic/Icon/Icon';
import { twJoin } from 'tailwind-merge';

export const FilterGroupIcon: FC<{ isOpen: boolean }> = ({ isOpen }) => (
    <Icon
        iconType="icon"
        icon="Arrow"
        className={twJoin(
            'absolute right-0 top-1/2 -translate-y-1/2 rotate-0 select-none text-xs transition',
            isOpen && 'rotate-180',
        )}
    />
);
