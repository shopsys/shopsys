import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twMergeCustom } from 'utils/twMerge';

type ProductCompareButtonProps = {
    isMainVariant: boolean;
    isWithText?: boolean;
    isProductInComparison: boolean;
    onProductInComparisonClick: () => void;
};

const TEST_IDENTIFIER = 'compare-button';

export const ProductCompareButton: FC<ProductCompareButtonProps> = ({
    className,
    isMainVariant,
    isWithText,
    isProductInComparison,
    onProductInComparisonClick,
}) => {
    const t = useTypedTranslationFunction();

    if (isMainVariant) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex items-center', className)}>
            <div
                className="flex cursor-pointer items-center"
                data-testid={TEST_IDENTIFIER}
                title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
                onClick={onProductInComparisonClick}
            >
                <Icon
                    className={twMergeCustom('m-2 text-grey', isProductInComparison && 'text-green')}
                    iconType="icon"
                    icon="Compare"
                />
                {isWithText && (
                    <span className="ml-1">{isProductInComparison ? t('Remove from comparison') : t('Compare')}</span>
                )}
            </div>
        </div>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
