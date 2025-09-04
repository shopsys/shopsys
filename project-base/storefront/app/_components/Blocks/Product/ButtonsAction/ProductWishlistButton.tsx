'use client';

import { addProductToListAction } from 'app/_actions/addProductToListAction';
import { removeProductFromListAction } from 'app/_actions/removeProductFromListAction';
import { useUpdateProductListUuid } from 'app/_utils/productLists/useUpdateProductListUuid';
import { HeartFilledIcon } from 'components/Basic/Icon/HeartFilledIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { useProductList } from 'components/providers/ProductListProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeProductListTypeEnum } from 'graphql/types';
import { HTMLAttributes, useOptimistic, useTransition } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductWishlistButtonProps = {
    productUuid: string;
    isWithText?: boolean;
};

export const ProductWishlistButton: FC<ProductWishlistButtonProps & NativeProps> = ({ productUuid, isWithText }) => {
    const { t } = useTranslation();
    const {
        products: wishedProducts,
        uuid: wishlistUuid,
        addToList,
        removeFromList,
    } = useProductList(TypeProductListTypeEnum.Wishlist);
    const isInWishlist = wishedProducts.has(productUuid);
    const [optimisticIsInWishlist, addOptimisticWishlist] = useOptimistic(isInWishlist);
    const [isPending, startTransition] = useTransition();

    const updateWishlistUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Wishlist);

    const handleAddToWishlist = () => {
        if (isPending) {
            return;
        }

        startTransition(async () => {
            addOptimisticWishlist(!optimisticIsInWishlist);

            const addProductToListResult = await addProductToListAction({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: wishlistUuid,
                        type: TypeProductListTypeEnum.Wishlist,
                    },
                },
            });

            if (addProductToListResult.error) {
                showErrorMessage(t('Unable to add product to wishlist.'));
                addOptimisticWishlist(!optimisticIsInWishlist);
                return;
            }

            showSuccessMessage(t('The item has been added to your wishlist.'));
            addToList(productUuid);

            const newUuid = addProductToListResult.data?.AddProductToList.uuid;
            if (newUuid) {
                updateWishlistUuid(newUuid);
            }

            // TODO: broadcast channel
            // dispatchBroadcastChannel('refetchWishedProducts');
        });
    };

    const handleRemoveFromWishlist = () => {
        if (isPending) {
            return;
        }

        startTransition(async () => {
            addOptimisticWishlist(!optimisticIsInWishlist);

            const removeProductFromListResult = await removeProductFromListAction({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: wishlistUuid,
                        type: TypeProductListTypeEnum.Wishlist,
                    },
                },
            });

            if (removeProductFromListResult.error) {
                showErrorMessage(t('Unable to remove product from wishlist.'));
                addOptimisticWishlist(!optimisticIsInWishlist);
                return;
            }

            showSuccessMessage(t('The item has been removed from your wishlist.'));
            removeFromList(productUuid);

            if (!removeProductFromListResult.data?.RemoveProductFromList) {
                updateWishlistUuid(null);
            }

            // TODO: broadcast channel
            // dispatchBroadcastChannel('refetchWishedProducts');
        });
    };

    return (
        <div
            aria-disabled={isPending}
            title={optimisticIsInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            className={twMergeCustom(
                'text-input-placeholder-default hover:text-input-placeholder-hovered flex cursor-pointer items-center gap-2',
            )}
            onClick={optimisticIsInWishlist ? handleRemoveFromWishlist : handleAddToWishlist}
        >
            {optimisticIsInWishlist ? (
                <HeartFilledIcon className="text-activeIconFull size-6" />
            ) : (
                <HeartIcon className="size-6" />
            )}
            {isWithText && (
                <span className="text-sm">
                    {optimisticIsInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}
                </span>
            )}
        </div>
    );
};
