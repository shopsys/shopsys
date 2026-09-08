import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useEffect, useRef, useState } from 'react';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';

export const useComparisonProducts = (
    comparedProducts: TypeProductInProductListFragment[],
    onSaveOrder?: (uuids: string[]) => Promise<boolean>,
) => {
    const { width } = useGetWindowSize();
    const [productOrder, setProductOrder] = useState<string[]>([]);
    const latestOrder = useRef<string[]>([]);
    const pendingOrder = useRef<string[] | null>(null);
    const savingOrder = useRef(false);
    const [parameterOrder, setParameterOrder] = useState(comparedProducts.map((product) => product.uuid));
    useEffect(() => {
        setParameterOrder((previous) => [
            ...previous.filter((uuid) => comparedProducts.some((product) => product.uuid === uuid)),
            ...comparedProducts.filter((product) => !previous.includes(product.uuid)).map((product) => product.uuid),
        ]);
    }, [comparedProducts]);
    const parameterSourceProducts = [
        ...parameterOrder
            .map((uuid) => comparedProducts.find((product) => product.uuid === uuid))
            .filter((product): product is TypeProductInProductListFragment => !!product),
        ...comparedProducts.filter((product) => !parameterOrder.includes(product.uuid)),
    ];

    const reorderProducts = (uuids: string[]) => {
        latestOrder.current = uuids;
        setProductOrder(uuids);
    };

    const saveProductOrder = async () => {
        if (!onSaveOrder || latestOrder.current.length === 0) return;
        pendingOrder.current = latestOrder.current;
        if (savingOrder.current) return;
        savingOrder.current = true;
        try {
            while (pendingOrder.current) {
                const order = pendingOrder.current;
                pendingOrder.current = null;
                const success = await onSaveOrder(order);
                if (!success && !pendingOrder.current && latestOrder.current === order) {
                    setProductOrder([]);
                }
            }
        } finally {
            savingOrder.current = false;
        }
    };
    const orderedProducts = [
        ...productOrder
            .map((uuid) => comparedProducts.find((product) => product.uuid === uuid))
            .filter((product): product is TypeProductInProductListFragment => !!product),
        ...comparedProducts.filter((product) => !productOrder.includes(product.uuid)),
    ];
    const [mobileSelection, setMobileSelection] = useState<string[]>([]);
    const mobileProducts = mobileSelection
        .map((uuid) => comparedProducts.find((product) => product.uuid === uuid))
        .filter((product): product is TypeProductInProductListFragment => !!product);
    orderedProducts.forEach((product) => {
        if (mobileProducts.length < 2 && !mobileProducts.some((item) => item.uuid === product.uuid)) {
            mobileProducts.push(product);
        }
    });
    const visibleProducts = width > 0 && width < 768 ? mobileProducts : orderedProducts;

    const selectMobileProduct = (index: number, uuid: string) => {
        setMobileSelection(
            mobileProducts.map((product, productIndex) => (productIndex === index ? uuid : product.uuid)),
        );
    };

    return {
        mobileProducts,
        visibleProducts,
        selectMobileProduct,
        reorderProducts,
        saveProductOrder,
        parameterSourceProducts,
        canReorder: width >= 768 && comparedProducts.length > 1,
    };
};
