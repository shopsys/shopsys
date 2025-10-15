import { WatchdogIcon } from 'components/Basic/Icon/WatchdogIcon';
import { Button } from 'components/Forms/Button/Button';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const WatchdogPopup = dynamic(
    () => import('components/Blocks/Popup/WatchdogPopup').then((component) => component.WatchdogPopup),
    {
        ssr: false,
    },
);

type WatchDogButtonProps = {
    productName: string;
    productUuid: string | undefined;
    isInquiryType: boolean;
    availability: TypeAvailability;
    productIsSellingDenied: boolean | undefined;
};

export const WatchDogButton: FC<WatchDogButtonProps> = ({
    productName,
    productUuid,
    isInquiryType,
    availability,
    productIsSellingDenied,
    className,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const showWatchdogButton =
        productUuid &&
        !isInquiryType &&
        (availability.status === TypeAvailabilityStatusEnum.OutOfStock || productIsSellingDenied);

    if (!showWatchdogButton) {
        return null;
    }

    const openWatchDogPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();

        if (productUuid) {
            updatePortalContent(<WatchdogPopup productUuid={productUuid} />);
        }
    };

    return (
        <Button
            aria-haspopup="dialog"
            aria-label={t('Open watchdog popup for {{productName}}', { ns: 'accessibility', productName })}
            className={twJoin('whitespace-nowrap', className)}
            size="large"
            title={t('Watchdog popup')}
            variant="primary"
            onClick={openWatchDogPopup}
        >
            <WatchdogIcon className="size-6" />
            {t('Watch the goods')}
        </Button>
    );
};
