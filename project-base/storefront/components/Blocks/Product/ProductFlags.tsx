import { Flag } from 'components/Basic/Flag/Flag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { twMergeCustom } from 'utils/twMerge';
import { ProductVisibleItemsConfigType } from './ProductsList/ProductListItem';

const MAX_FLAGS = 5;

type ProductFlagsProps = {
    flags: TypeSimpleFlagFragment[];
    percentageDiscount: number | null;
    variant: 'grid' | 'gridHeader' | 'list' | 'detail' | 'comparison' | 'bestsellers';
    visibleItemsConfig?: ProductVisibleItemsConfigType;
};

type ProductFlagItem =
    | { type: 'flag'; flag: TypeSimpleFlagFragment }
    | { type: 'discount'; percentageDiscount: number };

export const ProductFlags: FC<ProductFlagsProps> = ({
    className,
    flags,
    percentageDiscount,
    variant,
    visibleItemsConfig = { flags: true, discount: false },
}) => {
    const { t } = useTranslation();
    const currentFilterQuery = useCurrentFilterQuery();
    const defaultSelectedFlags = useSessionStore((state) => state.defaultProductFiltersMap.flags);

    const isValidDiscountPercentage = percentageDiscount !== null && percentageDiscount > 0 && percentageDiscount < 100;
    const hasVisibleDiscount = visibleItemsConfig.discount && isValidDiscountPercentage;
    const isProductList = variant !== 'detail' && variant !== 'comparison';
    const activeFlagUuids =
        isProductList && variant !== 'bestsellers'
            ? [...Array.from(defaultSelectedFlags), ...(currentFilterQuery?.flags ?? [])]
            : [];
    const visibleFlags = visibleItemsConfig.flags ? flags : [];
    const activeFlags = visibleFlags.filter((flag) => activeFlagUuids.includes(flag.uuid));
    const remainingFlags = visibleFlags.filter((flag) => !activeFlagUuids.includes(flag.uuid));
    const orderedItems: ProductFlagItem[] = [
        ...activeFlags.map((flag) => ({ type: 'flag' as const, flag })),
        ...(hasVisibleDiscount
            ? [{ type: 'discount' as const, percentageDiscount: percentageDiscount as number }]
            : []),
        ...remainingFlags.map((flag) => ({ type: 'flag' as const, flag })),
    ];
    const visibleItems = variant === 'detail' ? orderedItems : orderedItems.slice(0, MAX_FLAGS);

    if (visibleItems.length === 0) {
        return null;
    }

    const variantTwClass = {
        grid: 'top-2.5 sm:top-5 left-2.5 sm:left-5 z-above',
        gridHeader: 'top-0 left-0',
        list: 'flex-row relative flex-wrap gap-2',
        detail: 'top-0 left-0',
        comparison: 'top-0 left-0',
        bestsellers: 'flex-row relative flex-wrap mb-1 gap-2',
    };

    return (
        <div
            className={twMergeCustom(
                'absolute flex max-w-full flex-col items-start gap-1',
                variantTwClass[variant],
                className,
            )}
        >
            {visibleItems.map((item) => {
                if (item.type === 'discount') {
                    return (
                        <Flag key="discount" className="max-w-full" type="discount">
                            <span className="wrap-break-word min-w-0">
                                -{item.percentageDiscount}% {t('disount')}
                            </span>
                        </Flag>
                    );
                }

                const { uuid, name, rgbColor } = item.flag;

                return (
                    <Flag key={uuid} className="max-w-full" rgbBgColor={rgbColor}>
                        {variant === 'gridHeader' || variant === 'comparison' ? (
                            <span className="wrap-break-word line-clamp-2">{name}</span>
                        ) : (
                            <span className="wrap-break-word min-w-0">{name}</span>
                        )}
                    </Flag>
                );
            })}
        </div>
    );
};
