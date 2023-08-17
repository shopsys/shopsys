import { CategoriesByColumnFragmentApi } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { DropdownItem } from './DropdownItem';
type PrimaryListProps = {
    navigationItems: CategoriesByColumnFragmentApi[];
};

export const PrimaryList: FC<PrimaryListProps> = ({ navigationItems }) => (
    <>
        {navigationItems.map((navigationItem, index) => (
            <DropdownItem key={index} navigationItem={navigationItem} index={index} goToMenu="secondary" />
        ))}
    </>
);
