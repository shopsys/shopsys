import { BellIcon } from 'components/Basic/Icon/BellIcon';
import { Button } from 'components/Forms/Button/Button';
import { getButtonIconClassName } from 'components/Forms/Button/buttonUtils';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { WatchDogProductType } from 'types/product';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showWatchdogButton } from 'utils/product/showWatchdogButton';

const WatchdogPopup = dynamic(
    () => import('components/Blocks/Popup/WatchdogPopup').then((component) => component.WatchdogPopup),
    {
        ssr: false,
    },
);

type WatchDogButtonProps = {
    className?: string;
    label?: string;
    product: WatchDogProductType;
    listIndex?: number;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
};

export const WatchDogButton: FC<WatchDogButtonProps> = ({ product, listIndex, size = 'xlarge', className, label }) => {
    const { t } = useTranslation();
    const buttonLabel = label ?? t('Notify me when available');
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
            aria-label={`${buttonLabel}: ${product.fullName}`}
            className={twJoin('whitespace-normal', className)}
            tid={TIDs.blocks_product_watchdog_button}
            title={buttonLabel}
            size={size}
            variant="primary"
            onClick={openWatchDogPopup}
        >
            <BellIcon aria-hidden="true" className={getButtonIconClassName(size)} />
            {buttonLabel}
        </Button>
    );
};
