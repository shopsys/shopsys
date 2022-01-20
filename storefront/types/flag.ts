import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductEdgesType } from 'types/product';
import { SimpleFlagFragmentApi } from 'graphql/generated';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    products: ListedProductEdgesType;
};

export type SimpleFlagType = SimpleFlagFragmentApi;
