import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useEffect, useRef, useState } from 'react';
import { mobileFirstSizes } from 'utils/mediaQueries';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';

export const useComparisonProducts = (
    comparedProducts: TypeProductInProductListFragment[],
    onMoveProduct?: (productUuid: string, afterProductUuid: string | null) => Promise<boolean>,
) => {
    const { width } = useGetWindowSize();
    const [productOrder, setProductOrder] = useState<string[]>([]);
    const latestOrder = useRef<string[]>([]);
    const pendingMoves = useRef<{ productUuid: string; afterProductUuid: string | null }[]>([]);
    const lastMovedOrder = useRef<string[]>([]);
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

    const saveProductMove = async (productUuid: string) => {
        const order = latestOrder.current;
        const index = order.indexOf(productUuid);
        if (!onMoveProduct || index === -1) return;
        pendingMoves.current.push({ productUuid, afterProductUuid: order[index - 1] ?? null });
        lastMovedOrder.current = order;
        if (savingOrder.current) return;
        savingOrder.current = true;
        try {
            for (let move = pendingMoves.current.shift(); move; move = pendingMoves.current.shift()) {
                await onMoveProduct(move.productUuid, move.afterProductUuid);
            }
            // Show the server order unless the customer started another drag meanwhile
            if (latestOrder.current === lastMovedOrder.current) {
                latestOrder.current = [];
                setProductOrder([]);
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
    const visibleProducts = width > 0 && width < mobileFirstSizes.md ? mobileProducts : orderedProducts;

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
        saveProductMove,
        parameterSourceProducts,
        canReorder: width >= mobileFirstSizes.md && comparedProducts.length > 1,
    };
};
