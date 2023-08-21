import { Icon } from 'components/Basic/Icon/Icon';
import { RemoveThin } from 'components/Basic/Icon/IconsSvg';
import { useComparison } from 'hooks/comparison/useComparison';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';

type ProductComparisonButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ProductComparisonButtonRemoveAll: FC<ProductComparisonButtonRemoveAllProps> = ({ displayMobile }) => {
    const t = useTypedTranslationFunction();
    const { handleCleanComparison } = useComparison();

    return (
        <div
            className={twJoin(
                'hidden cursor-pointer items-center rounded bg-greyVeryLight py-2 px-4 transition-colors hover:bg-greyLighter sm:inline-flex',
                displayMobile && 'mb-5 inline-flex sm:hidden',
            )}
            onClick={handleCleanComparison}
        >
            <span className="mr-3 text-sm">{t('Delete all')}</span>
            <Icon icon={<RemoveThin />} className="w-3" />
        </div>
    );
};
