import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useState } from 'react';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';

export const useComparisonProducts = (comparedProducts: TypeProductInProductListFragment[]) => {
    const { width } = useGetWindowSize();
    const [mobileSelection, setMobileSelection] = useState<string[]>([]);
    const mobileProducts = mobileSelection
        .map((uuid) => comparedProducts.find((product) => product.uuid === uuid))
        .filter((product): product is TypeProductInProductListFragment => !!product);
    comparedProducts.forEach((product) => {
        if (mobileProducts.length < 2 && !mobileProducts.some((item) => item.uuid === product.uuid)) {
            mobileProducts.push(product);
        }
    });
    const visibleProducts = width > 0 && width < 768 ? mobileProducts : comparedProducts;

    const selectMobileProduct = (index: number, uuid: string) => {
        setMobileSelection(
            mobileProducts.map((product, productIndex) => (productIndex === index ? uuid : product.uuid)),
        );
    };

    return { mobileProducts, visibleProducts, selectMobileProduct };
};
