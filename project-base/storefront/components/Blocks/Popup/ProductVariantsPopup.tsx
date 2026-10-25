import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import type { ProductVariantsPopupContentProps } from './ProductVariantsPopupContent';
import { ProductVariantsPopupSkeleton } from './ProductVariantsPopupSkeleton';

const ProductVariantsPopupContent = dynamic(
    () => import('./ProductVariantsPopupContent').then((component) => component.ProductVariantsPopupContent),
    { ssr: false, loading: () => <ProductVariantsPopupSkeleton /> },
);

type ProductVariantsPopupProps = ProductVariantsPopupContentProps & {
    productSlug: string;
    productName: string;
};

export const ProductVariantsPopup: FC<ProductVariantsPopupProps> = ({
    productUuid,
    productSlug,
    productName,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    onVariantCompared,
}) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <Popup
            className="h-[min(52rem,calc(100dvh-2rem))] max-h-[calc(100dvh-2rem)] vl:max-h-[85vh] overflow-hidden"
            contentClassName="flex min-h-0 flex-1 flex-col"
            title={onVariantCompared ? t('Choose a variant to compare') : t('Choose a variant')}
        >
            <div className="mb-4 flex shrink-0 flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <p className="wrap-break-word min-w-0 flex-1 text-sm text-text-less">{productName}</p>

                <LinkButton
                    className="group shrink-0"
                    href={`${productSlug}#${PRODUCT_VARIANTS_ID}`}
                    variant="tertiary"
                    onClick={closePortalContent}
                    aria-label={t('Go to page with product variants of {{ productName }}', {
                        ns: 'accessibility',
                        productName,
                    })}
                >
                    {t('Product detail')}
                    <ArrowSecondaryIcon
                        aria-hidden="true"
                        className="size-3 shrink-0 -rotate-90 transition-transform duration-200 motion-safe:group-hover:translate-x-0.5 motion-reduce:transition-none"
                    />
                </LinkButton>
            </div>

            <div className="-mr-2 min-h-0 overflow-y-auto pr-2">
                <ProductVariantsPopupContent
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={listIndex}
                    productUuid={productUuid}
                    onVariantCompared={onVariantCompared}
                />
            </div>
        </Popup>
    );
};
