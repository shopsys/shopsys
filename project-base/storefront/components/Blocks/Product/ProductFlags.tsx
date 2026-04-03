import { Flag } from 'components/Basic/Flag/Flag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ProductVisibleItemsConfigType } from './ProductsList/ProductListItem';

type ProductFlagsProps = {
    flags: TypeSimpleFlagFragment[];
    percentageDiscount: number | null;
    variant: 'list' | 'detail' | 'comparison' | 'bestsellers';
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
        list: 'top-5 right-2.5 sm:right-5 items-end z-above',
        detail: 'top-3 right-0 items-end',
        comparison: 'top-3 left-5',
        bestsellers: 'flex-row relative flex-wrap mb-1 gap-2',
    };

    return (
        <div className={twMergeCustom('absolute flex flex-col items-start gap-1', variantTwClass[variant])}>
            {visibleItemsConfig.flags &&
                flags.length > 0 &&
                flags.map(({ uuid, name, rgbColor }) => {
                    return (
                        <Flag key={uuid} rgbBgColor={rgbColor}>
                            {name}
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
