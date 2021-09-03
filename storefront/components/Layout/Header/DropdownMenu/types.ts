import { NavigationItem as NavigationItemType } from '../../../../connectors/navigation/Navigation';

export type DropdownListLevels = 'primary' | 'secondary' | 'tertiary';

export type DropdownSlideToType = 'left' | 'right';

export type DropdownIndexType = number | string;

export type DropdownItemType = {
    changeState?: any;
    goToMenu?: DropdownListLevels;
    slideTo?: DropdownSlideToType;
    index?: DropdownIndexType;
};

export type DropdownListType = {
    navigationItems: NavigationItemType[];
    historyOfIndexes: (number | string)[];
};
