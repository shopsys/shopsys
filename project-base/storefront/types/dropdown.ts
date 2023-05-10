import { NavigationQueryApi } from 'graphql/generated';

export type DropdownListLevels = 'primary' | 'secondary' | 'tertiary';

export type DropdownItemType = {
    goToMenu?: DropdownListLevels;
    index?: number | string;
};

export type DropdownListProps = {
    navigationItems: NavigationQueryApi['navigation'];
    historyOfIndexes: (number | string | undefined)[];
};
