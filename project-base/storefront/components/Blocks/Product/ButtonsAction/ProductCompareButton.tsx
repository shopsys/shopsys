import { CompareIcon } from 'components/Basic/Icon/IconsSvg';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'helpers/twMerge';

type ProductCompareButtonProps = {
    isWithText?: boolean;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
};

const TEST_IDENTIFIER = 'compare-button';

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
            data-testid={TEST_IDENTIFIER}
            title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
            onClick={toggleProductInComparison}
        >
            <CompareIcon className={twMergeCustom('text-grey', isProductInComparison && 'text-green')} />
            {isWithText && <span>{t(isProductInComparison ? 'Remove from comparison' : 'Compare')}</span>}
        </div>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
