import { CompareFilledIcon } from 'components/Basic/Icon/CompareFilledIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ProductCompareButtonProps = {
    productName: string;
    isWithText?: boolean;
    isWithShortText?: boolean;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    tabIndex?: number;
};

export const ProductCompareButton: FC<ProductCompareButtonProps> = ({
    className,
    productName,
    isWithText,
    isWithShortText,
    isProductInComparison,
    toggleProductInComparison,
    tabIndex = 0,
}) => {
    const { t } = useTranslation();
    const buttonText = isWithShortText
        ? isProductInComparison
            ? t('In comparison')
            : t('Compare')
        : isProductInComparison
          ? t('Remove from comparison')
          : t('Compare');

    return (
        <button
            aria-haspopup={isProductInComparison ? 'dialog' : undefined}
            data-tid={TIDs.product_compare_button}
            tabIndex={tabIndex}
            title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            aria-label={
                isProductInComparison
                    ? t('Remove from comparison product {{ productName }}', {
                          productName: productName,
                          ns: 'accessibility',
                      })
                    : t('Add to comparison product {{ productName }}', {
                          ns: 'accessibility',
                          productName: productName,
                      })
            }
            className={twMergeCustom(
                'flex cursor-pointer items-center justify-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden',
                !isWithText && 'size-9',
                className,
            )}
            onClick={toggleProductInComparison}
        >
            {isProductInComparison ? (
                <CompareFilledIcon className="size-6 shrink-0 text-icon-accent-red" />
            ) : (
                <CompareIcon className="size-6 shrink-0" />
            )}
            {isWithText && <span className="truncate text-sm">{buttonText}</span>}
        </button>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
