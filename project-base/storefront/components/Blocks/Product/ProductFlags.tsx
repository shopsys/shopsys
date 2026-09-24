import { Flag } from 'components/Basic/Flag/Flag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ProductVisibleItemsConfigType } from './ProductsList/ProductListItem';

const MAX_PRODUCT_LIST_FLAGS = 3;

type ProductFlagsProps = {
    flags: TypeSimpleFlagFragment[];
    percentageDiscount: number | null;
    variant: 'grid' | 'gridHeader' | 'list' | 'detail' | 'comparison' | 'bestsellers';
    visibleItemsConfig?: ProductVisibleItemsConfigType;
};

export const ProductFlags: FC<ProductFlagsProps> = ({
    flags,
    percentageDiscount,
    variant,
    visibleItemsConfig = { flags: true, discount: false },
}) => {
    const { t } = useTranslation();

    const hasVisibleFlags = visibleItemsConfig.flags && flags.length > 0;
    const isValidDiscountPercentage = percentageDiscount !== null && percentageDiscount > 0 && percentageDiscount < 100;
    const hasVisibleDiscount = visibleItemsConfig.discount && isValidDiscountPercentage;
    const isProductList = variant !== 'detail' && variant !== 'comparison';
    const visibleFlags = isProductList ? flags.slice(0, MAX_PRODUCT_LIST_FLAGS - (hasVisibleDiscount ? 1 : 0)) : flags;

    if (!hasVisibleFlags && !hasVisibleDiscount) {
        return null;
    }

    const variantTwClass = {
        grid: 'top-2.5 sm:top-5 left-2.5 sm:left-5 z-above',
        gridHeader: 'relative',
        list: 'flex-row relative flex-wrap gap-2',
        detail: 'top-0 left-0',
        comparison: 'top-3 left-5',
        bestsellers: 'flex-row relative flex-wrap mb-1 gap-2',
    };

    return (
        <div className={twMergeCustom('absolute flex max-w-full flex-col items-start gap-1', variantTwClass[variant])}>
            {visibleItemsConfig.flags &&
                flags.length > 0 &&
                visibleFlags.map(({ uuid, name, rgbColor }) => {
                    return (
                        <Flag key={uuid} className="max-w-full" rgbBgColor={rgbColor}>
                            {variant === 'gridHeader' ? (
                                <span className="wrap-break-word line-clamp-2">{name}</span>
                            ) : (
                                <span className="wrap-break-word min-w-0">{name}</span>
                            )}
                        </Flag>
                    );
                })}

            {hasVisibleDiscount && (
                <Flag className="max-w-full" type="discount">
                    <span className="wrap-break-word min-w-0">
                        -{percentageDiscount}% {t('disount')}
                    </span>
                </Flag>
            )}
        </div>
    );
};
