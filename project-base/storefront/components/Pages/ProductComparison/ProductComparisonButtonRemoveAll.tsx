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
                'hidden cursor-pointer items-center rounded py-2 px-4 transition-colors sm:inline-flex border-2',
                displayMobile && 'mb-5 inline-flex sm:hidden',
                'bg-actionInvertedBackground border-actionInvertedBorder text-actionInvertedText',
                'hover:bg-actionInvertedBackgroundHovered hover:border-actionInvertedBorderHovered hover:text-actionInvertedTextHovered',
                'active:bg-actionInvertedBackgroundActive active:border-actionInvertedBorderActive active:text-actionInvertedTextActive',
            )}
            onClick={handleRemoveComparison}
        >
            <span className="mr-3 text-sm">{t('Delete all')}</span>
            <RemoveThinIcon className="w-3" />
        </div>
    );
};
