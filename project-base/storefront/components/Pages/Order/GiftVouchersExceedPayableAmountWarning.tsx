import { Alert } from 'components/Basic/Alert/Alert';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type GiftVouchersExceedPayableAmountWarningProps = {
    cartContainsGiftVoucherProducts: boolean;
};

export const GiftVouchersExceedPayableAmountWarning: FC<GiftVouchersExceedPayableAmountWarningProps> = ({
    cartContainsGiftVoucherProducts,
}) => {
    const { t } = useTranslation();

    return (
        <Alert
            icon={WarningIcon}
            tid={TIDs.pages_order_gift_vouchers_exceed_payable_amount_warning}
            title={t('The order cannot be completed')}
            variant="error"
        >
            <span className="text-text-less">
                {t('The value of the applied gift vouchers exceeds the amount that can be paid with them.')}{' '}
                {cartContainsGiftVoucherProducts &&
                    `${t('Gift vouchers cannot be used to pay for other gift vouchers.')} `}
                {t('Remove a gift voucher or add more items to the cart.')}
            </span>
        </Alert>
    );
};
