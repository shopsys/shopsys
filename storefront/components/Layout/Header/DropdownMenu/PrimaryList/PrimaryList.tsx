import DropdownItem from 'components/Layout/Header/DropdownMenu/Item';
import { FC } from 'react';
import { NavigationItem as NavigationItemType } from 'connectors/navigation/Navigation';

type PrimaryListType = {
    navigationItems: NavigationItemType[];
};

const PrimaryList: FC<PrimaryListType> = (props) => {
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
