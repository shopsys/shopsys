import { CompareFilledIcon } from 'components/Basic/Icon/CompareFilledIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type ProductCompareButtonProps = {
    productName: string;
    isWithText?: boolean;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    tabIndex?: number;
};

export const ProductCompareButton: FC<ProductCompareButtonProps> = ({
    className,
    productName,
    isWithText,
    isProductInComparison,
    toggleProductInComparison,
    tabIndex = 0,
}) => {
    const { t } = useTranslation();

    return (
        <button
            aria-haspopup="dialog"
            tabIndex={tabIndex}
            title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            aria-label={
                isProductInComparison
                    ? t('Remove product {{ productName }} from comparison', {
                          productName: productName,
                      })
                    : t('Add product {{ productName }} to comparison', {
                          productName: productName,
                      })
            }
            className={twMergeCustom(
                'text-icon-less hover:text-icon-accent flex cursor-pointer items-center gap-2',
                'rounded-sm outline-none',
                className,
            )}
            onClick={toggleProductInComparison}
        >
            {isProductInComparison ? (
                <CompareFilledIcon className="text-icon-accent-red size-6" />
            ) : (
                <CompareIcon className="size-6" />
            )}
            {isWithText && (
                <span className="text-sm">{isProductInComparison ? t('Remove from comparison') : t('Compare')}</span>
            )}
        </button>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
