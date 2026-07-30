import { CompareFilledIcon } from 'components/Basic/Icon/CompareFilledIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
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
    const tooltipLabel = isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison');

    const ariaLabel = isProductInComparison
        ? t('Remove from comparison product {{ productName }}', {
              productName: productName,
              ns: 'accessibility',
          })
        : t('Add to comparison product {{ productName }}', {
              ns: 'accessibility',
              productName: productName,
          });
    const ComparisonIcon = isProductInComparison ? CompareFilledIcon : CompareIcon;

    if (!isWithText) {
        return (
            <IconButton
                Icon={ComparisonIcon}
                ariaLabel={ariaLabel}
                className={className}
                iconClassName={isProductInComparison ? 'text-icon-accent-red' : undefined}
                shape="rounded"
                tabIndex={tabIndex}
                tid={TIDs.product_compare_button}
                title={tooltipLabel}
                tooltipLabel={tooltipLabel}
                variant="ghost"
                onClick={toggleProductInComparison}
            />
        );
    }

    return (
        <button
            data-tid={TIDs.product_compare_button}
            tabIndex={tabIndex}
            title={tooltipLabel}
            aria-label={ariaLabel}
            className={twMergeCustom(
                'flex cursor-pointer items-center justify-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden transition-colors',
                className,
            )}
            onClick={toggleProductInComparison}
        >
            <ComparisonIcon
                aria-hidden="true"
                className={twMergeCustom('size-6 shrink-0', isProductInComparison && 'text-icon-accent-red')}
            />
            <span className="truncate text-sm">{buttonText}</span>
        </button>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
