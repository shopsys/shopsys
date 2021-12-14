import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';

export type FlagDetailType = {
    __typename: 'Flag';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    products: ListedProductEdgesType;
};
