import { HeartFilledIcon } from 'components/Basic/Icon/HeartFilledIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'className'>;

type ProductCompareButtonProps = {
    productName: string;
    isWithText?: boolean;
    isWithShortText?: boolean;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
    tabIndex?: number;
};

export const ProductWishlistButton: FC<ProductCompareButtonProps & NativeProps> = ({
    className,
    productName,
    isWithText,
    isWithShortText,
    isProductInWishlist,
    toggleProductInWishlist,
    tabIndex = 0,
}) => {
    const { t } = useTranslation();
    const buttonText = isWithShortText
        ? isProductInWishlist
            ? t('In wishlist')
            : t('To wishlist')
        : isProductInWishlist
          ? t('Remove from wishlist')
          : t('Add to wishlist');
    const tooltipLabel = isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist');
    const ariaLabel = isProductInWishlist
        ? t('Remove from wishlist product {{ productName }}', {
              ns: 'accessibility',
              productName: productName,
          })
        : t('Add to wishlist product {{ productName }}', {
              ns: 'accessibility',
              productName: productName,
          });
    const WishlistIcon = isProductInWishlist ? HeartFilledIcon : HeartIcon;

    if (!isWithText) {
        return (
            <IconButton
                Icon={WishlistIcon}
                ariaLabel={ariaLabel}
                className={className}
                iconClassName={isProductInWishlist ? 'text-icon-accent-red' : undefined}
                shape="rounded"
                tabIndex={tabIndex}
                tid={TIDs.product_wishlist_button}
                title={tooltipLabel}
                tooltipLabel={tooltipLabel}
                variant="ghost"
                onClick={toggleProductInWishlist}
            />
        );
    }

    return (
        <button
            data-tid={TIDs.product_wishlist_button}
            tabIndex={tabIndex}
            title={tooltipLabel}
            aria-label={ariaLabel}
            className={twMergeCustom(
                'flex cursor-pointer items-center justify-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden transition-colors',
                className,
            )}
            onClick={toggleProductInWishlist}
        >
            <WishlistIcon
                aria-hidden="true"
                className={twMergeCustom('size-6 shrink-0', isProductInWishlist && 'text-icon-accent-red')}
            />
            <span className="truncate text-sm">{buttonText}</span>
        </button>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
