import { NavigationQueryApi } from 'graphql/requests/navigation/queries/NavigationQuery.generated';

export type DropdownListLevels = 'primary' | 'secondary' | 'tertiary';

export type DropdownItemType = {
    goToMenu?: DropdownListLevels;
    index?: number | string;
};

export type DropdownListProps = {
    navigationItems: NavigationQueryApi['navigation'];
    historyOfIndexes: (number | string | undefined)[];
};
