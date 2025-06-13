import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useComparison } from 'utils/productLists/comparison/useComparison';

type ProductComparisonButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ProductComparisonButtonRemoveAll: FC<ProductComparisonButtonRemoveAllProps> = ({ displayMobile }) => {
    const { t } = useTranslation();
    const { removeComparison: handleRemoveComparison } = useComparison();

    return (
        <Button
            aria-label={t('Remove all products from comparison')}
            className={twJoin(displayMobile && 'mb-5 inline-flex sm:hidden')}
            variant="inverted"
            onClick={handleRemoveComparison}
        >
            {t('Remove all')}
            <RemoveIcon className="size-3" />
        </Button>
    );
};
