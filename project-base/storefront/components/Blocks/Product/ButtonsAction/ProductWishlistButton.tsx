import { Heart } from 'components/Basic/Icon/IconsSvg';
import useTranslation from 'next-translate/useTranslation';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'helpers/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    isWithText?: boolean;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
};

const TEST_IDENTIFIER = 'wishlist-button';

export const ProductWishlistButton: FC<ProductCompareButtonProps & NativeProps> = ({
    className,
    isWithText,
    isProductInWishlist,
    toggleProductInWishlist,
}) => {
    const { t } = useTranslation();

    return (
        <div className={twMergeCustom('flex items-center', className)}>
            <div
                className="flex cursor-pointer items-center"
                data-testid={TEST_IDENTIFIER}
                title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
                onClick={toggleProductInWishlist}
            >
                <Heart
                    isFull={isProductInWishlist}
                    className={twMergeCustom('m-2', 'text-grey', isProductInWishlist && 'text-green')}
                />
                {isWithText && (
                    <span className="ml-1">
                        {isProductInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}
                    </span>
                )}
            </div>
        </div>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
