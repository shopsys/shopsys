import { DropdownItem } from './DropdownItem';
import { CategoriesByColumnFragmentApi } from 'graphql/generated';

type PrimaryListProps = {
    navigationItems: CategoriesByColumnFragmentApi[];
};

export const PrimaryList: FC<PrimaryListProps> = ({ navigationItems }) => (
    <>
        {navigationItems.map((navigationItem, index) => (
            <DropdownItem key={index} goToMenu="secondary" index={index} navigationItem={navigationItem} />
        ))}
    </>
);
