import { CompareFilledIcon } from 'components/Basic/Icon/CompareFilledIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type ProductCompareButtonProps = {
    isWithText?: boolean;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
};

export const ProductCompareButton: FC<ProductCompareButtonProps> = ({
    className,
    isWithText,
    isProductInComparison,
    toggleProductInComparison,
}) => {
    const { t } = useTranslation();

    return (
        <div
            title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            className={twMergeCustom(
                'flex cursor-pointer items-center gap-2 p-2 text-link hover:text-linkHovered',
                className,
            )}
            onClick={toggleProductInComparison}
        >
            {isProductInComparison ? (
                <CompareFilledIcon className="size-6  text-activeIconFull" />
            ) : (
                <CompareIcon className="size-6" />
            )}
            {isWithText && (
                <span className="text-sm">{isProductInComparison ? t('Remove from comparison') : t('Compare')}</span>
            )}
        </div>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
