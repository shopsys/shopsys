import { OperationContext } from '@urql/core';
import { useEffect } from 'react';
import { useRouter } from 'next/router';

export const useRefreshCartOnNavigation = (
    refreshCart: (opts?: Partial<OperationContext> | undefined) => void,
    isCartEmpty: boolean,
): void => {
    const router = useRouter();

    useEffect(() => {
        if (!isCartEmpty) {
            refreshCart();
        }
    }, [router.asPath, isCartEmpty]);
};
