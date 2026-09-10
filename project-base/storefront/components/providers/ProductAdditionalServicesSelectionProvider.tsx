import { createContext, useContext, useState } from 'react';

type ProductAdditionalServicesSelectionContextType = {
    clearPendingServiceUuids: (productUuid: string) => void;
    isAddToCartFlowPendingByProductUuid: Record<string, boolean>;
    pendingServiceUuidsByProductUuid: Record<string, string[]>;
    setIsAddToCartFlowPending: (productUuid: string, isPending: boolean) => void;
    updatePendingServiceUuids: (productUuid: string, updater: (serviceUuids: string[]) => string[]) => void;
};

const ProductAdditionalServicesSelectionContext = createContext<ProductAdditionalServicesSelectionContextType | null>(
    null,
);

export const ProductAdditionalServicesSelectionProvider: FC = ({ children }) => {
    const [pendingServiceUuidsByProductUuid, setPendingServiceUuidsByProductUuid] = useState<Record<string, string[]>>(
        {},
    );
    const [isAddToCartFlowPendingByProductUuid, setIsAddToCartFlowPendingByProductUuid] = useState<
        Record<string, boolean>
    >({});

    const updatePendingServiceUuids = (productUuid: string, updater: (serviceUuids: string[]) => string[]) => {
        setPendingServiceUuidsByProductUuid((currentServiceUuidsByProductUuid) => ({
            ...currentServiceUuidsByProductUuid,
            [productUuid]: updater(currentServiceUuidsByProductUuid[productUuid] ?? []),
        }));
    };

    const clearPendingServiceUuids = (productUuid: string) => {
        setPendingServiceUuidsByProductUuid((currentServiceUuidsByProductUuid) => ({
            ...currentServiceUuidsByProductUuid,
            [productUuid]: [],
        }));
    };

    const setIsAddToCartFlowPending = (productUuid: string, isPending: boolean) => {
        setIsAddToCartFlowPendingByProductUuid((currentPendingState) => {
            if (isPending) {
                return { ...currentPendingState, [productUuid]: true };
            }

            const updatedPendingState = { ...currentPendingState };
            delete updatedPendingState[productUuid];

            return updatedPendingState;
        });
    };

    return (
        <ProductAdditionalServicesSelectionContext.Provider
            value={{
                clearPendingServiceUuids,
                isAddToCartFlowPendingByProductUuid,
                pendingServiceUuidsByProductUuid,
                setIsAddToCartFlowPending,
                updatePendingServiceUuids,
            }}
        >
            {children}
        </ProductAdditionalServicesSelectionContext.Provider>
    );
};

export const useProductAdditionalServicesSelectionContext = () => useContext(ProductAdditionalServicesSelectionContext);
