import { Alert } from 'components/Basic/Alert/Alert';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const PaidByGiftVoucherInfo: FC = () => {
    const { t } = useTranslation();

    return (
        <Alert
            icon={CheckmarkIcon}
            tid={TIDs.pages_order_paid_by_gift_voucher_info}
            title={t('The order will be paid by the gift voucher')}
            variant="success"
        >
            <span className="text-text-less">
                {t('Applied gift vouchers cover the whole order, no other payment is needed.')}
            </span>
        </Alert>
    );
};
