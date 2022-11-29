import { SimpleFlagFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductConnectionPreviewType } from 'types/product';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    productConnection: ListedProductConnectionPreviewType;
};

export type SimpleFlagType = SimpleFlagFragmentApi;
