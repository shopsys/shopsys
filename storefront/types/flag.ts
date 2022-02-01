import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductConnectionType } from 'types/product';
import { SimpleFlagFragmentApi } from 'graphql/generated';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    productConnection: ListedProductConnectionType;
};

export type SimpleFlagType = SimpleFlagFragmentApi;
