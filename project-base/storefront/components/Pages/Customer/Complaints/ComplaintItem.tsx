import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import {
    CustomerRecordCard,
    CustomerRecordColumnInfo,
    CustomerRecordProductImage,
    CustomerRecordRowInfo,
} from 'components/Pages/Customer/CustomerRecordElements';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeComplaintListItemFragment } from 'graphql/requests/complaints/fragments/ComplaintListItemFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type ComplaintItemProps = {
    complaintItem: TypeComplaintListItemFragment;
};

export const ComplaintItem: FC<ComplaintItemProps> = ({ complaintItem }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const { url } = useDomainConfig();
    const [customerComplaintDetailUrl] = getInternationalizedStaticUrls(['/customer/complaint-detail'], url);
    const complaintDetailLink = {
        pathname: customerComplaintDetailUrl,
        query: { complaintNumber: complaintItem.number },
    };

    return (
        <CustomerRecordCard>
            <div className="flex flex-1 flex-col gap-2.5">
                <div className="flex vl:flex-row flex-col gap-x-8 gap-y-2">
                    <CustomerRecordColumnInfo tid={TIDs.complaint_list_item_number} title={t('Complaint number')}>
                        <ExtendedNextLink
                            className="font-semibold text-sm"
                            type="complaintDetail"
                            aria-label={t('Go to complaint number {{complaintNumber}}', {
                                ns: 'accessibility',
                                complaintNumber: complaintItem.number,
                            })}
                            href={complaintDetailLink}
                        >
                            {complaintItem.number}
                        </ExtendedNextLink>
                    </CustomerRecordColumnInfo>

                    <CustomerRecordColumnInfo tid={TIDs.complaint_list_item_date} title={t('Creation date')}>
                        {formatDate(complaintItem.createdAt)}
                    </CustomerRecordColumnInfo>

                    <CustomerRecordColumnInfo title={t('Status')}>{complaintItem.status}</CustomerRecordColumnInfo>

                    <CustomerRecordColumnInfo title={t('Resolution')}>
                        {complaintItem.resolution.name}
                    </CustomerRecordColumnInfo>
                </div>

                <CustomerRecordRowInfo title={t('Products')}>
                    <div className="flex flex-wrap gap-3">
                        {complaintItem.items.map((item) => (
                            <ComplaintProduct key={item.uuid} complaintProduct={item} />
                        ))}
                    </div>
                </CustomerRecordRowInfo>
            </div>

            <div className="flex shrink-0 gap-4">
                <LinkButton
                    type="complaintDetail"
                    aria-label={t('Go to complaint number {{complaintNumber}}', {
                        ns: 'accessibility',
                        complaintNumber: complaintItem.number,
                    })}
                    variant="secondary"
                    href={complaintDetailLink}
                >
                    {t('Detail')}
                </LinkButton>
            </div>
        </CustomerRecordCard>
    );
};

type ComplaintProductProps = {
    complaintProduct: TypeComplaintListItemFragment['items'][number];
};

const ComplaintProduct: FC<ComplaintProductProps> = ({ complaintProduct }) => {
    const productName = complaintProduct.productName;

    return (
        <CustomerRecordProductImage
            image={complaintProduct.product?.mainImage?.url}
            imageAlt={complaintProduct.product?.mainImage?.name ?? productName}
            isVisible={complaintProduct.product?.isVisible}
            link={complaintProduct.product?.slug}
            quantity={complaintProduct.quantity}
            tid={TIDs.complaint_item_image}
            tooltipLabel={productName}
        />
    );
};
