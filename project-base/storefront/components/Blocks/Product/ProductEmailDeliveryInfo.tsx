import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

/**
 * Replaces the delivery options link for products delivered by email, which have no delivery options to choose from
 */
export const ProductEmailDeliveryInfo: FC = ({ className }) => {
    const { t } = useTranslation();

    return (
        <p
            className={twMergeCustom('flex w-fit items-center gap-1 text-sm text-text-less', className)}
            data-tid={TIDs.product_detail_email_delivery_info}
        >
            <MailIcon className="size-6 shrink-0" />

            <span>{t('Delivered electronically by email')}</span>
        </p>
    );
};
