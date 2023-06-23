import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    isMainVariant: boolean;
    isWithText?: boolean;
    isInWishlist: boolean;
    handleProductInWishlist: () => void;
};

const TEST_IDENTIFIER = 'wishlist-button';

export const ProductWishlistButton: FC<ProductCompareButtonProps & NativeProps> = ({
    className,
    isMainVariant,
    isWithText,
    isInWishlist,
    handleProductInWishlist,
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
                title={isInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
                onClick={handleProductInWishlist}
            >
                <Icon
                    className={twMergeCustom('m-2', 'text-grey', isInWishlist && 'text-green')}
                    iconType="icon"
                    icon={isInWishlist ? 'HeartFull' : 'Heart'}
                />
                {isWithText && (
                    <span className="ml-1">{isInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}</span>
                )}
            </div>
        </div>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
