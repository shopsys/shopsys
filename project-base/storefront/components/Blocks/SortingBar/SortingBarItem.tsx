import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { twJoin } from 'tailwind-merge';

type SortingBarItemProps = { isActive: boolean; onClick?: () => void };

export const SortingBarItem: FC<SortingBarItemProps> = ({ children, isActive, onClick }) => {
    return (
        <LabelLink
            className={twJoin('max-vl:bg-backgroundAccentLess max-vl:text-text', isActive && 'max-vl:text-textAccent')}
            isActive={isActive}
            onClick={onClick}
        >
            {children}
        </LabelLink>
    );
};
