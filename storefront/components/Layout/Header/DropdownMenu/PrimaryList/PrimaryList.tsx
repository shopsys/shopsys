import DropdownItem from 'components/Layout/Header/DropdownMenu/Item';
import { FC } from 'react';
import { NavigationItem as NavigationItemType } from 'types/navigation';

type PrimaryListProps = {
    navigationItems: NavigationItemType[];
};

const PrimaryList: FC<PrimaryListProps> = (props) => {
    return (
        <>
            {props.navigationItems.map((navigationItem, index) => (
                <DropdownItem key={index} navigationItem={navigationItem} index={index} goToMenu="secondary" />
            ))}
        </>
    );
};

/* @component */
export default PrimaryList;
