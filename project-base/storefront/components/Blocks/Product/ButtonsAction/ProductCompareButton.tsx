import { CompareIcon } from 'components/Basic/Icon/IconsSvg';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';

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
            className={twMergeCustom('flex cursor-pointer items-center gap-2 p-2', className)}
            title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            onClick={toggleProductInComparison}
        >
            <CompareIcon className={twMergeCustom('text-grey', isProductInComparison && 'text-green')} />
            {isWithText && <span>{isProductInComparison ? t('Remove from comparison') : t('Compare')}</span>}
        </div>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
