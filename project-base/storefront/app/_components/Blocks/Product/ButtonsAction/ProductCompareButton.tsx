'use client';

import { addProductToListAction } from 'app/_actions/addProductToListAction';
import { removeProductFromListAction } from 'app/_actions/removeProductFromListAction';
import { useUpdateProductListUuid } from 'app/_utils/productLists/useUpdateProductListUuid';
import { CompareFilledIcon } from 'components/Basic/Icon/CompareFilledIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { useProductList } from 'components/providers/ProductListProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeProductListTypeEnum } from 'graphql/types';
import dynamic from 'next/dynamic';
import { HTMLAttributes, useOptimistic, useTransition } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { twMergeCustom } from 'utils/twMerge';

const ProductComparePopup = dynamic(() =>
    import('app/_components/Blocks/Popup/ProductComparePopup').then((component) => component.ProductComparePopup),
);
type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    productUuid: string;
    isWithText?: boolean;
};

export const ProductCompareButton: FC<ProductCompareButtonProps & NativeProps> = ({ productUuid, isWithText }) => {
    const { t } = useTranslation();
    const {
        products: wishedProducts,
        uuid: wishlistUuid,
        addToList,
        removeFromList,
    } = useProductList(TypeProductListTypeEnum.Comparison);
    const isInComparison = wishedProducts.has(productUuid);
    const [optimisticIsInComparison, addOptimisticComparison] = useOptimistic(isInComparison);
    const [isPending, startTransition] = useTransition();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const updateComparisonUuid = useUpdateProductListUuid(TypeProductListTypeEnum.Comparison);

    const handleAddToComparison = () => {
        if (isPending) {
            return;
        }

        startTransition(async () => {
            addOptimisticComparison(!optimisticIsInComparison);

            const addProductToListResult = await addProductToListAction({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: wishlistUuid,
                        type: TypeProductListTypeEnum.Comparison,
                    },
                },
            });

            if (addProductToListResult.error) {
                showErrorMessage(t('Unable to add product to comparison.'));
                addOptimisticComparison(!optimisticIsInComparison);
                return;
            }

            updatePortalContent(<ProductComparePopup />);
            addToList(productUuid);

            const newUuid = addProductToListResult.data?.AddProductToList.uuid;
            if (newUuid) {
                updateComparisonUuid(newUuid);
            }

            // TODO: broadcast channel
            // dispatchBroadcastChannel('refetchWishedProducts');
        });
    };

    const handleRemoveFromComparison = () => {
        if (isPending) {
            return;
        }

        startTransition(async () => {
            addOptimisticComparison(!optimisticIsInComparison);

            const removeProductFromListResult = await removeProductFromListAction({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: wishlistUuid,
                        type: TypeProductListTypeEnum.Comparison,
                    },
                },
            });

            if (removeProductFromListResult.error) {
                showErrorMessage(t('Unable to remove product from comparison.'));
                addOptimisticComparison(!optimisticIsInComparison);
                return;
            }

            showSuccessMessage(t('Product has been removed from your comparison.'));
            removeFromList(productUuid);

            if (!removeProductFromListResult.data?.RemoveProductFromList) {
                updateComparisonUuid(null);
            }

            // TODO: broadcast channel
            // dispatchBroadcastChannel('refetchWishedProducts');
        });
    };

    return (
        <div
            aria-disabled={isPending}
            title={optimisticIsInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            className={twMergeCustom(
                'flex cursor-pointer items-center gap-2 text-inputPlaceholder hover:text-inputPlaceholderHovered',
            )}
            onClick={optimisticIsInComparison ? handleRemoveFromComparison : handleAddToComparison}
        >
            {optimisticIsInComparison ? (
                <CompareFilledIcon className="size-6 text-activeIconFull" />
            ) : (
                <CompareIcon className="size-6" />
            )}
            {isWithText && (
                <span className="text-sm">{optimisticIsInComparison ? t('Remove from comparison') : t('Compare')}</span>
            )}
        </div>
    );
};
