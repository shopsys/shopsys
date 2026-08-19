import { GridIcon } from 'components/Basic/Icon/GridIcon';
import { ListIcon } from 'components/Basic/Icon/ListIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { useCookiesStore } from 'store/useCookiesStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ProductListViewModeToggle: FC = () => {
    const { t } = useTranslation();
    const productListViewMode = useCookiesStore((store) => store.productListViewMode);
    const setCookiesStoreState = useCookiesStore((store) => store.setCookiesStoreState);

    return (
        <fieldset className="flex items-center">
            <legend className="sr-only">{t('Product list view mode', { ns: 'accessibility' })}</legend>

            <ProductListViewModeToggleButton
                Icon={GridIcon}
                ariaLabel={t('Show products in grid view', { ns: 'accessibility' })}
                isActive={productListViewMode === 'grid'}
                tid={TIDs.blocks_product_list_view_grid}
                title={t('Grid view')}
                onClick={() => setCookiesStoreState({ productListViewMode: 'grid' })}
            />

            <ProductListViewModeToggleButton
                Icon={ListIcon}
                ariaLabel={t('Show products in list view', { ns: 'accessibility' })}
                isActive={productListViewMode === 'list'}
                tid={TIDs.blocks_product_list_view_list}
                title={t('List view')}
                onClick={() => setCookiesStoreState({ productListViewMode: 'list' })}
            />
        </fieldset>
    );
};

type ProductListViewModeToggleButtonProps = {
    Icon: SvgFC;
    ariaLabel: string;
    isActive: boolean;
    tid: string;
    title: string;
    onClick: () => void;
};

const ProductListViewModeToggleButton: FC<ProductListViewModeToggleButtonProps> = ({
    Icon,
    ariaLabel,
    isActive,
    tid,
    title,
    onClick,
}) => (
    <IconButton
        Icon={Icon}
        aria-pressed={isActive}
        ariaLabel={ariaLabel}
        shape="rounded"
        size="small"
        tid={tid}
        title={title}
        tooltipLabel={title}
        variant="ghost"
        onClick={onClick}
    />
);
