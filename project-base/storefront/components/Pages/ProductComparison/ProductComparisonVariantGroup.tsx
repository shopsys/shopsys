import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { useOpenProductVariantsPopup } from 'components/Blocks/Popup/useOpenProductVariantsPopup';
import { Button } from 'components/Forms/Button/Button';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';

type ProductComparisonVariantGroupProps = {
    product: TypeProductInProductListFragment;
    listIndex: number;
    onRemove: () => void;
};

export const ProductComparisonVariantGroup: FC<ProductComparisonVariantGroupProps> = ({
    product,
    listIndex,
    onRemove,
}) => {
    const { t } = useTranslation();
    const isRemovingReplacedFamily = useRef(false);
    const { toggleProductInComparison } = useComparison({
        onProductRemoveError: () => {
            isRemovingReplacedFamily.current = false;
        },
    });
    const openProductVariantsPopup = useOpenProductVariantsPopup();

    // The stored family cannot be compared, so it is replaced by the first variant chosen for it.
    // The popup stays open, so later variants must not request the removal again.
    const removeReplacedFamily = () => {
        if (isRemovingReplacedFamily.current) {
            return;
        }

        isRemovingReplacedFamily.current = true;
        toggleProductInComparison(product, GtmProductListNameType.product_comparison_page, listIndex);
    };

    const chooseVariantToCompare = () =>
        openProductVariantsPopup(product, {
            gtmProductListName: GtmProductListNameType.product_comparison_page,
            listIndex,
            onVariantAddedToComparison: removeReplacedFamily,
        });

    return (
        <div
            className="flex flex-wrap items-center gap-4 rounded-xl bg-background-more p-4"
            data-comparison-variant-group
        >
            <div className="min-w-0 flex-1">
                <p className="font-semibold">{product.fullName}</p>
                <p className="mt-1 text-sm text-text-less">
                    {t('Choose a specific variant to compare its price and parameters.')}
                </p>
            </div>
            <Button onClick={chooseVariantToCompare}>{t('Choose a variant to compare')}</Button>
            <IconButton
                Icon={CloseIcon}
                ariaLabel={t('Remove from comparison product {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                title={t('Remove from comparison')}
                variant="ghost"
                onClick={onRemove}
            />
        </div>
    );
};
