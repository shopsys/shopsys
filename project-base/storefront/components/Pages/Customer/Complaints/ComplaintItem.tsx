import { ComplaintItemColumnInfo } from './ComplaintItemColumnInfo';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type ComplaintItemProps = {
    complaintItem: TypeComplaintDetailFragment;
};

export const ComplaintItem: FC<ComplaintItemProps> = ({ complaintItem }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const { url } = useDomainConfig();
    const [customerComplaintDetailUrl] = getInternationalizedStaticUrls(['/customer/complaint-detail'], url);

    return (
        <div className="bg-background-more vl:p-6 flex flex-col gap-5 rounded-md p-4">
            <div className="vl:flex-row vl:items-start vl:justify-between flex flex-col gap-4">
                <div className="flex size-20 shrink-0" data-tid={TIDs.complaint_item_image}>
                    <Image
                        priority
                        alt={complaintItem.items[0].orderItem?.product?.mainImage?.name || ''}
                        className="size-20 object-contain mix-blend-multiply"
                        height={48}
                        src={complaintItem.items[0].product?.mainImage?.url}
                        width={72}
                    />
                </div>

                <div className="flex flex-col gap-1">
                    <span className="h5">
                        {complaintItem.items[0].product?.isVisible ? (
                            <ExtendedNextLink
                                href={complaintItem.items[0].product.slug}
                                type="product"
                                aria-label={t('Go to product {{productName}}', {
                                    ns: 'accessibility',
                                    productName: complaintItem.items[0].productName,
                                })}
                            >
                                {complaintItem.items[0].productName}
                            </ExtendedNextLink>
                        ) : (
                            complaintItem.items[0].productName
                        )}
                    </span>

                    <div className="flex flex-wrap gap-x-8 gap-y-2">
                        <ComplaintItemColumnInfo
                            title={t('Complaint number')}
                            value={
                                <ExtendedNextLink
                                    type="complaintDetail"
                                    aria-label={t('Go to complaint number {{complaintNumber}}', {
                                        ns: 'accessibility',
                                        complaintNumber: complaintItem.number,
                                    })}
                                    href={{
                                        pathname: customerComplaintDetailUrl,
                                        query: { complaintNumber: complaintItem.number },
                                    }}
                                >
                                    {complaintItem.number}
                                </ExtendedNextLink>
                            }
                        />

                        <ComplaintItemColumnInfo
                            title={t('Creation date')}
                            value={formatDate(complaintItem.createdAt)}
                        />

                        <ComplaintItemColumnInfo
                            title={t('Status')}
                            value={complaintItem.status}
                            wrapperClassName="min-w-[80px]"
                        />
                    </div>
                </div>

                <LinkButton
                    className="w-full whitespace-nowrap md:ml-auto md:w-auto"
                    size="small"
                    type="complaintDetail"
                    aria-label={t('Go to complaint number {{complaintNumber}}', {
                        ns: 'accessibility',
                        complaintNumber: complaintItem.number,
                    })}
                    href={{
                        pathname: customerComplaintDetailUrl,
                        query: { complaintNumber: complaintItem.number },
                    }}
                >
                    {t('Complaint detail')}
                </LinkButton>
            </div>
        </div>
    );
};
