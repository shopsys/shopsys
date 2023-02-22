import { Icon } from 'components/Basic/Icon/Icon';
import ProductButtonsActionPopup from 'components/Blocks/Popup/ProductButtonsActionPopup/ProductButtonsActionPopup';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, HTMLAttributes } from 'react';
import { twMerge } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ButtonsActionProps = {
    productUuid: string;
    isMainVariant: boolean;
    iconsOnly?: boolean;
};

const TEST_IDENTIFIER = 'buttons-action';

export const ButtonsAction: FC<ButtonsActionProps & NativeProps> = ({
    productUuid,
    className,
    isMainVariant,
    iconsOnly = true,
}) => {
    const t = useTypedTranslationFunction();
    const { isProductInComparison, handleProductInComparison, isPopupCompareOpen, setIsPopupCompareOpen } =
        useHandleCompare(productUuid);

    if (isMainVariant) {
        return null;
    }

    return (
        <>
            <div className={twMerge('mb-2 flex justify-end', className)} data-testid={TEST_IDENTIFIER}>
                <div
                    className="mr-2 flex cursor-pointer last:mr-0"
                    title={isProductInComparison ? t('Remove product from comparison') : t('Add product to comparison')}
                    onClick={handleProductInComparison}
                >
                    <Icon
                        className={twMerge('text-grey', isProductInComparison && 'text-green')}
                        iconType="icon"
                        icon="Compare"
                    />
                    {!iconsOnly ? (isProductInComparison ? t('Remove from comparison') : t('Compare')) : ''}
                </div>
            </div>

            <ProductButtonsActionPopup
                isVisible={isPopupCompareOpen}
                onCloseCallback={() => setIsPopupCompareOpen(false)}
            />
        </>
    );
};

ButtonsAction.displayName = 'ButtonsAction';
