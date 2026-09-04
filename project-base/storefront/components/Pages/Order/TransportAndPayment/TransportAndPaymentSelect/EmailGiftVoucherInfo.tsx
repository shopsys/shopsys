import { Alert } from 'components/Basic/Alert/Alert';
import { GiftIcon } from 'components/Basic/Icon/GiftIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type EmailGiftVoucherInfoProps = {
    isSingular: boolean;
    description: string | null;
};

export const EmailGiftVoucherInfo: FC<EmailGiftVoucherInfoProps> = ({ isSingular, description }) => {
    const { t } = useTranslation();

    const title = isSingular ? t('We send the gift voucher by email') : t('We send gift vouchers by email');

    return (
        <Alert icon={GiftIcon} tid={TIDs.pages_order_email_gift_voucher_info} title={title} variant="info">
            {description && <span className="text-text-less">{description}</span>}
        </Alert>
    );
};
