import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useMemo } from 'react';
import { TypeContentDocument } from 'types/contentEditor';
import { collectProductCatnums } from 'utils/contentEditor/collectProductCatnums';

const NO_PRODUCTS: TypeListedProductFragment[] = [];

export const useDocumentProducts = (document: TypeContentDocument | null) => {
    const catnums = useMemo(() => collectProductCatnums(document), [document]);
    const [{ data, fetching }] = useProductsByCatnums({ variables: { catnums }, pause: catnums.length === 0 });

    return { products: data?.productsByCatnums ?? NO_PRODUCTS, areProductsFetching: fetching };
};
