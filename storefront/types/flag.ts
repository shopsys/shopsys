import { ListedProductConnectionPreviewFragmentApi, SimpleFlagFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    productConnection: ListedProductConnectionPreviewFragmentApi;
};

export type SimpleFlagType = SimpleFlagFragmentApi;
