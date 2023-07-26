import { useEffect } from 'react';
import {
    ListedProductFragmentApi,
    useAddProductToWishlistMutationApi,
    useCleanWishlistMutationApi,
    useRemoveProductFromWishlistMutationApi,
    useSharedWishlistQueryApi,
    useWishlistQueryApi,
} from 'graphql/generated';
import { useTypedTranslationFunction } from './typescript/useTypedTranslationFunction';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/toasts';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { useQueryError } from './graphQl/useQueryError';

export const useWishlist = () => {
    const t = useTypedTranslationFunction();

    const { isUserLoggedIn } = useCurrentUserData();

    const updateWishlistUuid = usePersistStore((s) => s.updateWishlistUuid);
    const clearWishlistUuid = usePersistStore((s) => s.clearWishlistUuid);
    const wishlistUuid = usePersistStore((s) => s.wishlistUuid);

    const [, addProductToWishlist] = useAddProductToWishlistMutationApi();
    const [, removeProductFromWishlist] = useRemoveProductFromWishlistMutationApi();
    const [, cleanWishlist] = useCleanWishlistMutationApi();
    const [{ data, fetching }] = useQueryError(
        useWishlistQueryApi({
            variables: { wishlistUuid },
            pause: !wishlistUuid,
        }),
    );

    const handleCleanWishlist = async () => {
        const cleanWishlistResult = await cleanWishlist({ wishlistUuid });

        if (cleanWishlistResult.error) {
            showErrorMessage(t('Unable to clean wishlist.'));
        } else {
            showSuccessMessage(t('Wishlist was cleaned.'));
            clearWishlistUuid();
        }
    };

    const handleAddToWishlist = async (productUuid: string) => {
        const addProductToWishlistResult = await addProductToWishlist({
            productUuid,
            wishlistUuid,
        });

        if (addProductToWishlistResult.error) {
            showErrorMessage(t('Unable to add product to wishlist.'));
        } else {
            showSuccessMessage(t('The item has been added to your wishlist.'));
            if (isUserLoggedIn) {
                clearWishlistUuid();
            } else {
                updateWishlistUuid(addProductToWishlistResult.data?.addProductToWishlist.uuid ?? '');
            }
        }
    };

    const handleRemoveFromWishlist = async (productUuid: string) => {
        const removeProductFromWishlistResult = await removeProductFromWishlist({ productUuid, wishlistUuid });

        if (removeProductFromWishlistResult.error) {
            showErrorMessage(t('Unable to remove product from wishlist.'));
        } else {
            if (!removeProductFromWishlistResult.data?.removeProductFromWishlist) {
                clearWishlistUuid();
            }
            showSuccessMessage(t('The item has been removed from your wishlist.'));
        }
    };

    const isProductInWishlist = (productUuid: string) =>
        !!data?.wishlist?.products.find((product) => product.uuid === productUuid);

    const toggleProductInWishlist = (productUuid: string) => {
        if (isProductInWishlist(productUuid)) {
            handleRemoveFromWishlist(productUuid);
        } else {
            handleAddToWishlist(productUuid);
        }
    };

    useEffect(() => {
        if (!isUserLoggedIn && data?.wishlist && wishlistUuid !== data.wishlist.uuid) {
            updateWishlistUuid(data.wishlist.uuid);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data?.wishlist?.uuid]);

    return {
        wishlist: data?.wishlist || undefined,
        fetching,
        isProductInWishlist,
        handleCleanWishlist,
        toggleProductInWishlist,
    };
};

export const useSharedWishlist = (catnums: string[]): { products: ListedProductFragmentApi[]; fetching: boolean } => {
    const [{ data, fetching }] = useQueryError(
        useSharedWishlistQueryApi({
            variables: { catnums },
        }),
    );

    if (!data?.productsByCatnums) {
        return { products: [], fetching };
    }

    return {
        products: data.productsByCatnums,
        fetching,
    };
};
