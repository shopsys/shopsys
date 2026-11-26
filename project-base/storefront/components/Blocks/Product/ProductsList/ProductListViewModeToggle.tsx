import { GridIcon } from 'components/Basic/Icon/GridIcon';
import { ListIcon } from 'components/Basic/Icon/ListIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { useCookiesStore } from 'store/useCookiesStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ProductListViewModeToggle: FC = () => {
    const { t } = useTranslation();
    const productListViewMode = useCookiesStore((store) => store.productListViewMode);
    const setCookiesStoreState = useCookiesStore((store) => store.setCookiesStoreState);

    return (
        // A segmented control: the pressed option is a raised surface inside a muted track, so no border has to frame
        // both options. The 2px track padding keeps the group 44px high (36px from vl) like the sort button next to it.
        <fieldset className="relative m-0 flex min-w-0 items-center gap-0.5 rounded-button border-0 bg-background-more p-0.5">
            <legend className="sr-only">{t('Product list view mode', { ns: 'accessibility' })}</legend>

            {/* the raised surface of the pressed option slides between the two equally wide options */}
            <span
                aria-hidden="true"
                className={twJoin(
                    'pointer-events-none absolute top-0.5 left-0.5 size-10 vl:size-8 rounded-detail bg-background-default shadow-xs motion-safe:transition-transform motion-safe:duration-200 motion-safe:ease-out',
                    productListViewMode === 'list' && 'translate-x-[calc(100%+0.125rem)]',
                )}
            />

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
        className="relative size-10 vl:size-8 rounded-detail text-text-less not-aria-pressed:hover:bg-background-most hover:text-text-default aria-pressed:bg-transparent aria-pressed:text-link-default aria-pressed:active:bg-transparent aria-pressed:hover:bg-transparent"
        shape="rounded"
        size="small"
        tid={tid}
        title={title}
        tooltipLabel={title}
        variant="ghost"
        onClick={onClick}
    />
);
