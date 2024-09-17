import { RemoveThinIcon } from 'components/Basic/Icon/RemoveThinIcon';
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
        <div
            className={twJoin(
                'hidden cursor-pointer items-center rounded border-2 px-4 py-2 transition-colors sm:inline-flex',
                displayMobile && 'mb-5 inline-flex sm:hidden',
                'border-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText',
                'hover:border-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
                'active:border-actionInvertedBorderActive active:bg-actionInvertedBackgroundActive active:text-actionInvertedTextActive',
            )}
            onClick={handleRemoveComparison}
        >
            <span className="mr-3 text-sm">{t('Delete all')}</span>
            <RemoveThinIcon className="w-3" />
        </div>
    );
};
