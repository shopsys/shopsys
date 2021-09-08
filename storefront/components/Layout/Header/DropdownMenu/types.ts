import { NavigationItem as NavigationItemType } from '../../../../connectors/navigation/Navigation';

export type DropdownListLevels = 'primary' | 'secondary' | 'tertiary';

export type DropdownItemType = {
    goToMenu?: DropdownListLevels;
    index?: number | string;
};

export type DropdownListType = {
    navigationItems: NavigationItemType[];
    historyOfIndexes: (number | string | undefined)[];
};
