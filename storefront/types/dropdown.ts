import { NavigationItem as NavigationItemType } from 'types/navigation';

export type DropdownListLevels = 'primary' | 'secondary' | 'tertiary';

export type DropdownItemType = {
    goToMenu?: DropdownListLevels;
    index?: number | string;
};

export type DropdownListType = {
    navigationItems: NavigationItemType[];
    historyOfIndexes: (number | string | undefined)[];
};
