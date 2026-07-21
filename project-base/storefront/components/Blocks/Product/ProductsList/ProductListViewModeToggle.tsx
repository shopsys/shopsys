import { GridIcon } from 'components/Basic/Icon/GridIcon';
import { ListIcon } from 'components/Basic/Icon/ListIcon';
import { TIDs } from 'cypress/tids';
import { useCookiesStore } from 'store/useCookiesStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

export const ProductListViewModeToggle: FC = () => {
    const { t } = useTranslation();
    const productListViewMode = useCookiesStore((store) => store.productListViewMode);
    const setCookiesStoreState = useCookiesStore((store) => store.setCookiesStoreState);

    return (
        <fieldset className="flex items-center">
            <legend className="sr-only">{t('Product list view mode', { ns: 'accessibility' })}</legend>

            <ProductListViewModeToggleButton
                ariaLabel={t('Show products in grid view', { ns: 'accessibility' })}
                isActive={productListViewMode === 'grid'}
                tid={TIDs.blocks_product_list_view_grid}
                title={t('Grid view')}
                onClick={() => setCookiesStoreState({ productListViewMode: 'grid' })}
            >
                <GridIcon className="size-5" />
            </ProductListViewModeToggleButton>

            <ProductListViewModeToggleButton
                ariaLabel={t('Show products in list view', { ns: 'accessibility' })}
                isActive={productListViewMode === 'list'}
                tid={TIDs.blocks_product_list_view_list}
                title={t('List view')}
                onClick={() => setCookiesStoreState({ productListViewMode: 'list' })}
            >
                <ListIcon className="size-5" />
            </ProductListViewModeToggleButton>
        </fieldset>
    );
};

type ProductListViewModeToggleButtonProps = {
    ariaLabel: string;
    isActive: boolean;
    tid: string;
    title: string;
    onClick: () => void;
};

const ProductListViewModeToggleButton: FC<ProductListViewModeToggleButtonProps> = ({
    ariaLabel,
    children,
    isActive,
    tid,
    title,
    onClick,
}) => (
    <button
        aria-label={ariaLabel}
        aria-pressed={isActive}
        className={twMergeCustom(
            'flex size-9 cursor-pointer items-center justify-center rounded-sm text-icon-less transition hover:text-icon-accent',
            isActive && 'text-icon-accent',
        )}
        data-tid={tid}
        title={title}
        type="button"
        onClick={onClick}
    >
        {children}
    </button>
);
