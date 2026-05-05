import { WatchdogIcon } from 'components/Basic/Icon/WatchdogIcon';
import { Button } from 'components/Forms/Button/Button';
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

type WatchDogButtonProps = {
    product: WatchDogProductType;
    listIndex?: number;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
};

export const WatchDogButton: FC<WatchDogButtonProps> = ({ product, listIndex, className, buttonSize = 'large' }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const showWatchdogButton =
        product.uuid &&
        !product.isInquiryType &&
        (product.availability.status === TypeAvailabilityStatusEnum.OutOfStock || product.isSellingDenied);

    if (!showWatchdogButton) {
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
            size={buttonSize}
            title={t('Watchdog popup')}
            variant="primary"
            onClick={openWatchDogPopup}
        >
            <WatchdogIcon className="size-6" />
            {buttonSize === 'large' && t('Watch the goods')}
        </Button>
    );
};
