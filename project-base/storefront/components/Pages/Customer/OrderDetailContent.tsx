import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Row, Cell, CellHead, Table, CellMinor } from 'components/Basic/Table/Table';
import { OrderStatus } from 'components/Blocks/OrderStatus/OrderStatus';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { PaymentTypeEnum } from 'types/payment';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPacketeryTransport } from 'utils/packetery';

const CreateComplaintPopup = dynamic(
    () => import('components/Blocks/Popup/CreateComplaintPopup').then((component) => component.CreateComplaintPopup),
    {
        ssr: false,
    },
);

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openCreateComplaintPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        orderUuid: string,
        orderItem: TypeOrderDetailItemFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(<CreateComplaintPopup orderItem={orderItem} orderUuid={orderUuid} />);
    };

    return (
        <>
            <Webline className="mb-2">
                <div className="flex flex-col items-center justify-between lg:flex-row">
                    <div className="w-1/5" />
                    <h1 tid={TIDs.order_detail_number}>
                        {t('Order number')} {order.number}
                    </h1>
                    <div className="flex items-center justify-end lg:w-1/5">
                        <Button
                            className="lg:px-2 lg:py-1"
                            tid={TIDs.order_detail_repeat_order_button}
                            onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                        >
                            {t('Repeat order')}
                        </Button>
                    </div>
                </div>
            </Webline>

            <Webline className="lg:flex">
                <Table className="w-full">
                    <Row>
                        <CellHead>{t('Basic information')}</CellHead>
                    </Row>
                    <Row>
                        <Cell>
                            <div className="flex justify-between">
                                {t('Creation date')}
                                <span tid={TIDs.order_detail_creation_date}>
                                    {formatDateAndTime(order.creationDate)}
                                </span>
                            </div>
                        </Cell>
                    </Row>
                </Table>

                {!!order.trackingNumber && (
                    <Table className="max-lg:mt-10 w-full">
                        <Row>
                            <CellHead>{t('Tracking package')}</CellHead>
                        </Row>
                        <Row>
                            <Cell>
                                <div className="flex justify-between">
                                    {t('Package number')}
                                    {order.trackingUrl ? (
                                        <ExtendedNextLink href={order.trackingUrl} target="_blank">
                                            {order.trackingNumber}
                                        </ExtendedNextLink>
                                    ) : (
                                        order.trackingNumber
                                    )}
                                </div>
                            </Cell>
                        </Row>
                    </Table>
                )}

                <Table className="max-lg:mt-10 w-full">
                    <Row>
                        <CellHead>{t('Payment information')}</CellHead>
                    </Row>
                    <Row>
                        <Cell>
                            <div className="flex justify-between">
                                {t('Status')}
                                <OrderStatus order={order} />
                            </div>
                        </Cell>
                    </Row>
                </Table>
            </Webline>

            <Webline>
                {!order.isPaid && order.payment.type === PaymentTypeEnum.GoPay && (
                    <div className="flex justify-center mt-10">
                        <div className="lg:w-1/2 p-5 bg-backgroundMore rounded">
                            <PaymentsInOrderSelect
                                orderUuid={order.uuid}
                                paymentTransactionCount={order.paymentTransactionsCount}
                                withRedirectAfterChanging={false}
                            />
                        </div>
                    </div>
                )}
            </Webline>

            <Webline>
                {!!order.note && (
                    <Table tableClassName="table-fixed mt-10">
                        <Row>
                            <CellHead>{t('Your note')}</CellHead>
                        </Row>
                        <Row>
                            <Cell className="[overflow-wrap:anywhere]">{order.note}</Cell>
                        </Row>
                    </Table>
                )}

                <div className="grid lg:grid-cols-2 mt-10 gap-5">
                    <Table className="border-0 p-0">
                        <Row>
                            <CellHead colSpan={2}>{t('Delivery address')}</CellHead>
                        </Row>
                        <Row>
                            <CellMinor>{t('First name')}</CellMinor>
                            <Cell>{order.deliveryFirstName}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Last name')}</CellMinor>
                            <Cell>{order.deliveryLastName}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Phone')}:</CellMinor>
                            <Cell>{order.deliveryTelephone}</Cell>
                        </Row>

                        {(order.transport.isPersonalPickup ||
                            isPacketeryTransport(order.transport.transportType.code)) && (
                            <Row>
                                <CellHead colSpan={2}>{t('Pickup place')}</CellHead>
                            </Row>
                        )}

                        {!!order.deliveryCompanyName && (
                            <Row>
                                <CellMinor>{t('Company name')}</CellMinor>
                                <Cell>{order.deliveryCompanyName}</Cell>
                            </Row>
                        )}

                        <Row>
                            <CellMinor>{t('Street and house no.')}:</CellMinor>
                            <Cell>{order.deliveryStreet}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('City')}:</CellMinor>
                            <Cell>{order.deliveryCity}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Postcode')}:</CellMinor>
                            <Cell>{order.deliveryPostcode}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Country')}:</CellMinor>
                            <Cell>{order.deliveryCountry?.name}</Cell>
                        </Row>
                    </Table>

                    <Table className="border-0 p-0">
                        <Row>
                            <CellHead colSpan={2}>{t('Billing address')}</CellHead>
                        </Row>
                        {!!order.companyName && (
                            <>
                                <Row>
                                    <CellMinor>{t('Company name')}</CellMinor>
                                    <Cell>{order.companyName}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Company number')}</CellMinor>
                                    <Cell>{order.companyNumber}</Cell>
                                </Row>

                                {!!order.companyTaxNumber && (
                                    <Row>
                                        <CellMinor>{t('Tax number')}</CellMinor>
                                        <Cell>{order.companyTaxNumber}</Cell>
                                    </Row>
                                )}
                            </>
                        )}

                        {!!order.firstName && (
                            <>
                                <Row>
                                    <CellMinor>{t('First name')}</CellMinor>
                                    <Cell>{order.firstName}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Last name')}</CellMinor>
                                    <Cell>{order.lastName}</Cell>
                                </Row>
                            </>
                        )}

                        <Row>
                            <CellMinor>{t('Email')}</CellMinor>
                            <Cell>{order.email}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Phone')}</CellMinor>
                            <Cell>{order.telephone}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Street and house no.')}</CellMinor>
                            <Cell>{order.street}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('City')}</CellMinor>
                            <Cell>{order.city}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Postcode')}</CellMinor>
                            <Cell>{order.postcode}</Cell>
                        </Row>

                        <Row>
                            <CellMinor>{t('Country')}</CellMinor>
                            <Cell>{order.country.name}</Cell>
                        </Row>
                    </Table>
                </div>
                {!!order.items.length && (
                    <div className="mt-10">
                        <div className="h2 mb-3 text-center">{t('Your purchase')}</div>
                        <div className="overflow-x-auto">
                            <Table
                                className="min-w-[700px]"
                                head={
                                    <Row>
                                        <CellHead isWithoutWrap>{t('Product name')}</CellHead>
                                        <CellHead className="text-right">{t('Price per piece incl. VAT')}</CellHead>
                                        <CellHead className="text-right">{t('Amount')}</CellHead>
                                        <CellHead className="text-right min-w-16">{t('VAT')}</CellHead>
                                        <CellHead className="text-right">{t('Total price excl. VAT')}</CellHead>
                                        <CellHead className="text-right">{t('Total price incl. VAT')}</CellHead>
                                        <CellHead className="text-right">{t('Actions')}</CellHead>
                                    </Row>
                                }
                            >
                                {order.items.map((item, index) => (
                                    <Row key={index}>
                                        <Cell>{item.name}</Cell>
                                        <Cell className="text-right">{formatPrice(item.unitPrice.priceWithVat)}</Cell>
                                        <Cell className="text-right">
                                            {item.quantity} {item.unit}
                                        </Cell>
                                        <Cell className="nowrap text-right">{parseFloat(item.vatRate)} %</Cell>
                                        <Cell className="text-right">
                                            {formatPrice(item.totalPrice.priceWithoutVat)}
                                        </Cell>
                                        <Cell className="text-right">{formatPrice(item.totalPrice.priceWithVat)}</Cell>
                                        {item.type === TypeOrderItemTypeEnum.Product && (
                                            <Cell className="text-right">
                                                <Button
                                                    size="small"
                                                    variant="inverted"
                                                    onClick={(e) => openCreateComplaintPopup(e, order.uuid, item)}
                                                >
                                                    {t('Create complaint')}
                                                </Button>
                                            </Cell>
                                        )}
                                    </Row>
                                ))}
                            </Table>
                        </div>
                        <div className="w-full text-right text-lg md:text-2xl text-price">
                            {t('Total price including VAT')}: {formatPrice(order.totalPrice.priceWithVat)}
                        </div>
                    </div>
                )}
            </Webline>
        </>
    );
};
