import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import useTranslation from 'next-translate/useTranslation';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    isWithText?: boolean;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
};

export const ProductWishlistButton: FC<ProductCompareButtonProps & NativeProps> = ({
    className,
    isWithText,
    isProductInWishlist,
    toggleProductInWishlist,
}) => {
    const { t } = useTranslation();

    return (
        <div
            className={twMergeCustom('flex cursor-pointer items-center gap-2 p-2 text-primaryDark', className)}
            title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            onClick={toggleProductInWishlist}
        >
            <HeartIcon
                className={twMergeCustom(isProductInWishlist && 'text-secondary')}
                isFull={isProductInWishlist}
            />
            {isWithText && (
                <span className="text-sm">
                    {isProductInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}
                </span>
            )}
        </div>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
