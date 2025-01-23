'use client';

import { TypeProductListTypeEnum } from 'graphql/types';
import { createContext, ReactNode, useCallback, useContext, useState } from 'react';

export type ProductListState = {
    [K in TypeProductListTypeEnum]?: {
        products: Set<string>;
        uuid: string | null;
    };
};

type ProductListContextType = ProductListState & {
    addToList: (productUuid: string, listType: TypeProductListTypeEnum) => void;
    removeFromList: (productUuid: string, listType: TypeProductListTypeEnum) => void;
    updateListUuid: (uuid: string | null, listType: TypeProductListTypeEnum) => void;
};

const ProductListContext = createContext<ProductListContextType | undefined>(undefined);

type ProductListProviderProps = {
    children: ReactNode;
    initialState: ProductListState;
};

export const ProductListProvider: FC<ProductListProviderProps> = ({ children, initialState }) => {
    const [productListState, setProductListState] = useState<ProductListState>(initialState);

    const updateListUuid = useCallback((uuid: string | null, listType: TypeProductListTypeEnum) => {
        setProductListState((currentState) => {
            const listState = currentState[listType] ?? { products: new Set(), uuid: null };

            return {
                ...currentState,
                [listType]: {
                    ...listState,
                    uuid,
                },
            };
        });
    }, []);

    const addToList = useCallback((productUuid: string, listType: TypeProductListTypeEnum) => {
        setProductListState((currentState) => {
            const listState = currentState[listType] ?? { products: new Set(), uuid: null };

            return {
                ...currentState,
                [listType]: {
                    ...listState,
                    products: new Set(Array.from(listState.products).concat(productUuid)),
                },
            };
        });
    }, []);

    const removeFromList = useCallback((productUuid: string, listType: TypeProductListTypeEnum) => {
        setProductListState((currentState) => {
            const listState = currentState[listType];

            if (!listState) {
                return currentState;
            }

            const updatedProducts = new Set(listState.products);
            updatedProducts.delete(productUuid);

            return {
                ...currentState,
                [listType]: {
                    ...listState,
                    products: updatedProducts,
                },
            };
        });
    }, []);

    return (
        <ProductListContext.Provider
            value={{
                ...productListState,
                addToList,
                removeFromList,
                updateListUuid,
            }}
        >
            {children}
        </ProductListContext.Provider>
    );
};

export const useProductList = (listType: TypeProductListTypeEnum) => {
    const context = useContext(ProductListContext);

    if (!context) {
        throw new Error('useProductList must be used within ProductListProvider');
    }

    const listState = context[listType] ?? { products: new Set(), uuid: null };

    return {
        products: listState.products,
        uuid: listState.uuid,
        addToList: (productUuid: string) => context.addToList(productUuid, listType),
        removeFromList: (productUuid: string) => context.removeFromList(productUuid, listType),
        updateListUuid: (uuid: string | null) => context.updateListUuid(uuid, listType),
    };
};
