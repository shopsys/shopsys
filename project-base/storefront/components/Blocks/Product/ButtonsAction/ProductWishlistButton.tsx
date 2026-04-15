import { HeartFilledIcon } from 'components/Basic/Icon/HeartFilledIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { TIDs } from 'cypress/tids';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
            data-tid={TIDs.product_wishlist_button}
            tabIndex={tabIndex}
            title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            aria-label={
                isProductInWishlist
                    ? t('Remove from wishlist product {{ productName }}', {
                          ns: 'accessibility',
                          productName: productName,
                      })
                    : t('Add to wishlist product {{ productName }}', {
                          ns: 'accessibility',
                          productName: productName,
                      })
            }
            className={twMergeCustom(
                'flex cursor-pointer items-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden',
                className,
            )}
            onClick={toggleProductInWishlist}
        >
            {isProductInWishlist ? (
                <HeartFilledIcon className="size-6 text-icon-accent-red" />
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
