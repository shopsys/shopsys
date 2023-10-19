import { HeartIcon } from 'components/Basic/Icon/IconsSvg';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

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
        <div
            className={twMergeCustom('flex cursor-pointer items-center gap-2 p-2', className)}
            data-testid={TEST_IDENTIFIER}
            title={isProductInWishlist ? t('Remove product from wishlist') : t('Add product to wishlist')}
            onClick={toggleProductInWishlist}
        >
            <HeartIcon
                className={twMergeCustom('text-grey', isProductInWishlist && 'text-green')}
                isFull={isProductInWishlist}
            />
            {isWithText && <span>{isProductInWishlist ? t('Remove from wishlist') : t('Add to wishlist')}</span>}
        </div>
    );
};

ProductWishlistButton.displayName = 'ProductWishlistButton';
