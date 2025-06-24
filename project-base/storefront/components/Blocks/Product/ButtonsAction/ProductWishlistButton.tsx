import { HeartFilledIcon } from 'components/Basic/Icon/HeartFilledIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import useTranslation from 'next-translate/useTranslation';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    productName: string;
    isWithText?: boolean;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
    tabIndex?: number;
};

export const ProductWishlistButton: FC<ProductCompareButtonProps & NativeProps> = ({
    className,
    productName,
    isWithText,
    isProductInWishlist,
    toggleProductInWishlist,
    tabIndex = 0,
}) => {
    const { t } = useTranslation();

    return (
        <button
            tabIndex={tabIndex}
            title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            aria-label={
                isProductInWishlist
                    ? t('Remove product {{ productName }} from wishlist', {
                          productName: productName,
                      })
                    : t('Add product {{ productName }} to wishlist', {
                          productName: productName,
                      })
            }
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
