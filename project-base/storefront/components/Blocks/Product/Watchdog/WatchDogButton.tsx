import { WatchdogIcon } from 'components/Basic/Icon/WatchdogIcon';
import { Button, getButtonIconClassName } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { WatchDogProductType } from 'types/product';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const WatchdogPopup = dynamic(
    () => import('components/Blocks/Popup/WatchdogPopup').then((component) => component.WatchdogPopup),
    {
        ssr: false,
    },
);

export const showWatchdogButton = (product: WatchDogProductType): boolean =>
    !!product.uuid &&
    !product.isInquiryType &&
    (product.availability.status === TypeAvailabilityStatusEnum.OutOfStock ||
        product.availability.status === TypeAvailabilityStatusEnum.ExpectedRestock ||
        product.isSellingDenied);

type WatchDogButtonProps = {
    product: WatchDogProductType;
    listIndex?: number;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
};

export const WatchDogButton: FC<WatchDogButtonProps> = ({ product, listIndex, size = 'xlarge', className }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (!showWatchdogButton(product)) {
        return null;
    }

    const openWatchDogPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();

        if (product.uuid) {
            updatePortalContent(<WatchdogPopup listIndex={listIndex} product={product} />);
        }
    };

    return (
        <Button
            aria-haspopup="dialog"
            aria-label={t('Open watchdog popup for {{productName}}', {
                ns: 'accessibility',
                productName: product.fullName,
            })}
            className={twJoin('whitespace-nowrap', className)}
            tid={TIDs.blocks_product_watchdog_button}
            title={t('Watchdog popup')}
            size={size}
            variant="primary"
            onClick={openWatchDogPopup}
        >
            <WatchdogIcon className={getButtonIconClassName(size)} />
            {t('Watch the goods')}
        </Button>
    );
};
