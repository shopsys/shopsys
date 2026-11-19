import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { SkeletonButton } from 'components/Basic/Skeleton/SkeletonButton';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { getButtonIconClassName } from 'components/Forms/Button/buttonUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type SkeletonModuleProductDetailAddToCartProps = Pick<ButtonBaseProps, 'size'> & {
    isInPurchaseActionsRow?: boolean;
};

/**
 * Mirrors the Add to cart button in ProductDetailAddToCart, so the placeholder has the width of the translated label.
 */
export const SkeletonModuleProductDetailAddToCart: FC<SkeletonModuleProductDetailAddToCartProps> = ({
    size = 'xlarge',
    isInPurchaseActionsRow = false,
}) => {
    const { t } = useTranslation();

    return (
        <SkeletonButton
            className={isInPurchaseActionsRow ? 'w-full min-w-max flex-1' : 'w-full sm:max-w-60'}
            contentClassName={isInPurchaseActionsRow ? 'whitespace-nowrap' : 'whitespace-normal text-balance'}
            size={size}
        >
            <CartIcon className={getButtonIconClassName(size)} />
            {t('Add to cart')}
        </SkeletonButton>
    );
};
