import { ProductVariantsPopup } from 'components/Blocks/Popup/ProductVariantsPopup';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductVariantsButtonProps = {
    productUuid: string;
    productSlug: string;
    productName: string;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    skipKeyboardNavigation?: boolean;
};

export const ProductVariantsButton: FC<ProductVariantsButtonProps> = ({
    productUuid,
    productSlug,
    productName,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    buttonSize,
    skipKeyboardNavigation = false,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    const openProductVariantsPopup = (event: React.MouseEvent<HTMLButtonElement>) => {
        event.stopPropagation();
        storeCurrentFocus();
        updatePortalContent(
            <ProductVariantsPopup
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                listIndex={listIndex}
                productName={productName}
                productSlug={productSlug}
                productUuid={productUuid}
            />,
        );
    };

    return (
        <Button
            aria-haspopup="dialog"
            className="w-full"
            size={buttonSize}
            tabIndex={skipKeyboardNavigation ? -1 : 0}
            tid={TIDs.product_variants_button}
            aria-label={t('Choose a variant of {{ productName }}', {
                ns: 'accessibility',
                productName,
            })}
            onClick={openProductVariantsPopup}
        >
            {t('Choose')}
        </Button>
    );
};
