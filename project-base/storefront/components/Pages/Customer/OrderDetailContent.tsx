import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Row, Cell, CellHead, Table, CellMinor } from 'components/Basic/Table/Table';
import { OrderStatus } from 'components/Blocks/OrderStatus/OrderStatus';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();

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

            <Webline>
                <Table>
                    <Row>
                        <CellHead>{t('Basic information')}</CellHead>
                        {!!order.trackingNumber && <CellHead>{t('Tracking package')}</CellHead>}
                        {!!order.note && <CellHead>{t('Your note')}</CellHead>}
                        <CellHead>{t('Payment information')}</CellHead>
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

                        {!!order.trackingNumber && (
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
                        )}

                        {!!order.note && <Cell>{order.note}</Cell>}

                        <Cell>
                            <div className="flex justify-between">
                                {t('Status')}
                                <OrderStatus order={order} />
                            </div>
                        </Cell>
                    </Row>
                </Table>

                <div className="flex justify-center mt-10">
                    <div className="lg:w-1/2 p-5 bg-backgroundMore rounded">
                        {!order.isPaid && order.payment.type === PaymentTypeEnum.GoPay && (
                            <PaymentsInOrderSelect
                                orderUuid={order.uuid}
                                paymentTransactionCount={order.paymentTransactionsCount}
                                withRedirectAfterChanging={false}
                            />
                        )}
                    </div>
                </div>

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

                        {!!order.deliveryCompanyName && (
                            <Row>
                                <CellMinor>{t('Company name')}</CellMinor>
                                <Cell>{order.deliveryCompanyName}</Cell>
                            </Row>
                        )}

                        <Row>
                            <CellMinor>{t('Phone')}:</CellMinor>
                            <Cell>{order.deliveryTelephone}</Cell>
                        </Row>

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

                        <Table
                            className="overflow-x-auto"
                            head={
                                <Row>
                                    <CellHead isWithoutWrap>{t('Product name')}</CellHead>
                                    <CellHead className="text-right">{t('Price per piece incl. VAT')}</CellHead>
                                    <CellHead className="text-right">{t('Amount')}</CellHead>
                                    <CellHead className="text-right">{t('VAT')}</CellHead>
                                    <CellHead className="text-right">{t('Total price excl. VAT')}</CellHead>
                                    <CellHead className="text-right">{t('Total price incl. VAT')}</CellHead>
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
                                    <Cell className="text-right">{formatPrice(item.totalPrice.priceWithoutVat)}</Cell>
                                    <Cell className="text-right">{formatPrice(item.totalPrice.priceWithVat)}</Cell>
                                </Row>
                            ))}
                            <Row>
                                <Cell className="w-full text-right text-2xl text-price" colSpan={6}>
                                    {t('Total price including VAT')}: {formatPrice(order.totalPrice.priceWithVat)}
                                </Cell>
                            </Row>
                        </Table>
                    </div>
                )}
            </Webline>
        </>
    );
};
