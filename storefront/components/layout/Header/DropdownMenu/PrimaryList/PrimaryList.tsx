import DropdownItem from '../Item';
import { DropdownItemType } from '../types';
import { FC } from 'react';
import { NavigationItem as NavigationItemType } from '../../../../../connectors/navigation/Navigation';

type PrimaryListType = {
    navigationItems: NavigationItemType[];
};

const PrimaryList: FC<PrimaryListType & DropdownItemType> = (props) => {
    return (
        <>
            {props.navigationItems.map((navigationItem, index) => (
                <DropdownItem
                    key={index}
                    navigationItem={navigationItem}
                    index={index}
                    changeState={props.changeState}
                    goToMenu="secondary"
                    slideTo="right"
                />
            ))}
        </>
    );
};

/* @component */
export default PrimaryList;
