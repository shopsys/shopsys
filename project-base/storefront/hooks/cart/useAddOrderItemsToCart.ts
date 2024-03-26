import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useAddOrderItemsToCartMutation } from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated';
import { AddOrderItemsToCartInput } from 'graphql/types';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { showErrorMessage } from 'helpers/toasts';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { Dispatch, SetStateAction, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useAddOrderItemsToCart = (): {
    orderForPrefillingUuid: string | undefined;
    setOrderForPrefillingUuid: Dispatch<SetStateAction<string | undefined>>;
    addOrderItemsToEmptyCart: (orderUuid: string) => Promise<void>;
    mergeOrderItemsWithCurrentCart: (orderUuid: string, shouldMerge?: boolean) => Promise<void>;
    notAddedProductNames: string[] | undefined;
    setNotAddedProductNames: Dispatch<SetStateAction<string[] | undefined>>;
} => {
    const [orderForPrefillingUuid, setOrderForPrefillingUuid] = useState<string>();
    const [notAddedProductNames, setNotAddedProductNames] = useState<string[]>();
    const { cart } = useCurrentCart();
    const router = useRouter();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const [, addOrderItemsToCart] = useAddOrderItemsToCartMutation();
    const { t } = useTranslation();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const handleAddingItemsToCart = async (input: AddOrderItemsToCartInput) => {
        const response = await addOrderItemsToCart({ input });
        setOrderForPrefillingUuid(undefined);

        if (response.error) {
            showErrorMessage(t('Could not prefill your cart'));

            return;
        }

        const newCart = response.data?.AddOrderItemsToCart;
        updateCartUuid(newCart?.uuid ?? null);

        if (newCart) {
            const notAddedProducts = newCart.modifications.multipleAddedProductModifications.notAddedProducts;
            const addedAllProducts = notAddedProducts.length === 0;
            if (addedAllProducts) {
                router.push(cartUrl);
            } else {
                setNotAddedProductNames(notAddedProducts.map((product) => product.fullName));
            }
        }
    };

    const addOrderItemsToEmptyCart = async (orderUuid: string) => {
        if (cart?.items.length) {
            setOrderForPrefillingUuid(orderUuid);

            return;
        }
        handleAddingItemsToCart({ orderUuid, cartUuid: null, shouldMerge: false });
    };

    const mergeOrderItemsWithCurrentCart = async (orderUuid: string, shouldMerge = false) => {
        const cartUuid = shouldMerge && cart?.uuid ? cart.uuid : null;
        handleAddingItemsToCart({ orderUuid, cartUuid, shouldMerge });
    };

    return {
        orderForPrefillingUuid,
        setOrderForPrefillingUuid,
        addOrderItemsToEmptyCart,
        mergeOrderItemsWithCurrentCart,
        notAddedProductNames,
        setNotAddedProductNames,
    };
};
