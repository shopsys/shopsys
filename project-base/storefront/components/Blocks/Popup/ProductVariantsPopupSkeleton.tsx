import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { VARIANT_PURCHASE_CLASS_NAME, VARIANT_ROW_CLASS_NAME } from './productVariantsPopupStyles';

export const ProductVariantsPopupSkeleton: FC = () => {
    const { t } = useTranslation();

    return (
        <div
            data-tid={TIDs.product_variants_popup_loading}
            aria-label={t('Loading options', { ns: 'accessibility' })}
            role="status"
        >
            <div aria-hidden="true" className="flex flex-col gap-3">
                {['first', 'second', 'third', 'fourth', 'fifth', 'sixth'].map((row) => (
                    <div className={VARIANT_ROW_CLASS_NAME} key={row}>
                        <Skeleton className="size-16 sm:size-20" />
                        <div className="flex flex-col gap-3">
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-3 w-1/2" />
                        </div>
                        <div className={VARIANT_PURCHASE_CLASS_NAME}>
                            <Skeleton className="h-4 w-20 md:justify-self-end" />
                            <Skeleton className="col-start-1 xs:col-start-2 h-10 w-full md:col-start-1" />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};
