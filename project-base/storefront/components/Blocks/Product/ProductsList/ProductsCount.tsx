import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ProductsCountProps = {
    totalCount: number;
};

export const ProductsCount: FC<ProductsCountProps> = ({ totalCount, className }) => {
    const { t } = useTranslation();

    return (
        <span className={twMergeCustom('text-sm text-text-less', className)}>
            {totalCount} {t('products count', { count: totalCount })}
        </span>
    );
};
