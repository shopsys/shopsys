import { HeartFilledIcon } from 'components/Basic/Icon/HeartFilledIcon';
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
        <button
            tabIndex={0}
            title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            className={twMergeCustom(
                'text-icon-less hover:text-icon-accent flex cursor-pointer items-center gap-2',
                'rounded-sm outline-none',
                className,
            )}
            onClick={toggleProductInWishlist}
        >
            {isProductInWishlist ? (
                <HeartFilledIcon className="text-icon-accent-red size-6" />
            ) : (
                <HeartIcon className="size-6" />
            )}
            {isWithText && (
                <span className="text-sm">
                    {isProductInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}
                </span>
            )}
        </button>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
