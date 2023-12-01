import { RemoveThinIcon } from 'components/Basic/Icon/IconsSvg';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductComparisonButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ProductComparisonButtonRemoveAll: FC<ProductComparisonButtonRemoveAllProps> = ({ displayMobile }) => {
    const { t } = useTranslation();
    const { removeComparison: handleRemoveComparison } = useComparison();

    return (
        <div
            className={twJoin(
                'hidden cursor-pointer items-center rounded bg-greyVeryLight py-2 px-4 transition-colors hover:bg-greyLighter sm:inline-flex',
                displayMobile && 'mb-5 inline-flex sm:hidden',
            )}
            onClick={handleRemoveComparison}
        >
            <span className="mr-3 text-sm">{t('Delete all')}</span>
            <RemoveThinIcon className="w-3" />
        </div>
    );
};
