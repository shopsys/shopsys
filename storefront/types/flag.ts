import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductEdgesType } from 'types/product';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    products: ListedProductEdgesType;
};
