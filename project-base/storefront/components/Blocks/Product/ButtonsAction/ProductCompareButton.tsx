import { ProductComparePopup } from './ProductComparePopup';
import { Icon } from 'components/Basic/Icon/Icon';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    productUuid: string;
    isMainVariant: boolean;
    isWithText?: boolean;
};

const TEST_IDENTIFIER = 'compare-button';

export const ProductCompareButton: FC<ProductCompareButtonProps & NativeProps> = ({
    productUuid,
    className,
    isMainVariant,
    isWithText,
}) => {
    const t = useTypedTranslationFunction();
    const { isProductInComparison, handleProductInComparison, isPopupCompareOpen, setIsPopupCompareOpen } =
        useHandleCompare(productUuid);

    if (isMainVariant) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex items-center', className)}>
            <div
                className="flex cursor-pointer items-center"
                data-testid={TEST_IDENTIFIER}
                title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
                onClick={handleProductInComparison}
            >
                <Icon
                    className={twMergeCustom('text-grey', isProductInComparison && 'text-green')}
                    iconType="icon"
                    icon="Compare"
                />
                {isWithText && (
                    <span className="ml-1">{isProductInComparison ? t('Remove from comparison') : t('Compare')}</span>
                )}
            </div>

            <ProductComparePopup isVisible={isPopupCompareOpen} onCloseCallback={() => setIsPopupCompareOpen(false)} />
        </div>
    );
};

ProductCompareButton.displayName = 'ProductCompareButton';
