import { Flag } from 'components/Basic/Flag/Flag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ProductVisibleItemsConfigType } from './ProductsList/ProductListItem';

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
    const hasVisibleDiscount = visibleItemsConfig.discount && !!percentageDiscount;
    const isValidDiscountPercentage = percentageDiscount !== null && percentageDiscount > 0 && percentageDiscount < 100;

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
        <div className={twMergeCustom('absolute flex flex-col items-start gap-1', variantTwClass[variant])}>
            {visibleItemsConfig.flags &&
                flags.length > 0 &&
                flags.map(({ uuid, name, rgbColor }) => {
                    return (
                        <Flag
                            key={uuid}
                            className={variant === 'gridHeader' ? 'max-w-full' : undefined}
                            rgbBgColor={rgbColor}
                        >
                            {variant === 'gridHeader' ? (
                                <span className="wrap-break-word line-clamp-2">{name}</span>
                            ) : (
                                name
                            )}
                        </Flag>
                    );
                })}

            {visibleItemsConfig.discount && isValidDiscountPercentage && (
                <Flag type="discount">
                    -{percentageDiscount}% {t('disount')}
                </Flag>
            )}
        </div>
    );
};
