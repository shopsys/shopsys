import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';

const RemoveAllProductsPopup = dynamic(
    () =>
        import('components/Blocks/Popup/RemoveAllProductsPopup').then((component) => component.RemoveAllProductsPopup),
    {
        ssr: false,
    },
);

type ProductComparisonButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ProductComparisonButtonRemoveAll: FC<ProductComparisonButtonRemoveAllProps> = ({ displayMobile }) => {
    const { t } = useTranslation();
    const { removeComparison } = useComparison();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const handleRemoveAllClick = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                removeAllHandler={removeComparison}
                title={t('Do you really want to remove all products from comparison?')}
            />,
        );
    };

    return (
        <Button
            aria-label={t('Remove all products from comparison', { ns: 'accessibility' })}
            className={twJoin(displayMobile && 'mb-5 inline-flex sm:hidden')}
            variant="inverted"
            onClick={handleRemoveAllClick}
        >
            {t('Remove all')}
            <RemoveIcon className="size-3" />
        </Button>
    );
};
