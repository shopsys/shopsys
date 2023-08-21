import { Icon } from 'components/Basic/Icon/Icon';
import { HeartFull, Heart } from 'components/Basic/Icon/IconsSvg';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
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
    const t = useTypedTranslationFunction();

    return (
        <div className={twMergeCustom('flex items-center', className)}>
            <div
                className="flex cursor-pointer items-center"
                data-testid={TEST_IDENTIFIER}
                title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
                onClick={toggleProductInWishlist}
            >
                <Icon
                    className={twMergeCustom('m-2', 'text-grey', isProductInWishlist && 'text-green')}
                    icon={isProductInWishlist ? <HeartFull /> : <Heart />}
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
