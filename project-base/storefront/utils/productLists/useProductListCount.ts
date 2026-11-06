import { useProductListState } from 'components/providers/ProductListsProvider';
import { TypeProductListTypeEnum } from 'graphql/types';

export const useProductListCount = (productListType: TypeProductListTypeEnum): number =>
    useProductListState(productListType).count;
