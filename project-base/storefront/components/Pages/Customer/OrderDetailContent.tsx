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
import { twMergeCustom } from 'utils/twMerge';

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
                <div className="flex flex-col items-center justify-between lg:mb-4 lg:flex-row">
                    <div className="w-1/5" />
                    <h1 className="mb-3 lg:mb-0" tid={TIDs.order_detail_number}>
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
                    <Row className="flex flex-col md:flex-row">
                        <Cell className="flex-1">
                            <ColumnHeader>{t('Basic information')}</ColumnHeader>
                            <Table className="border-0 p-0">
                                <Row>
                                    <CellMinor>{t('Creation date')}</CellMinor>
                                    <Cell align="right">
                                        <span tid={TIDs.order_detail_creation_date}>
                                            {formatDateAndTime(order.creationDate)}
                                        </span>
                                    </Cell>
                                </Row>
                            </Table>
                        </Cell>

                        {!!order.trackingNumber && (
                            <Cell className="flex-1">
                                <ColumnHeader>{t('Tracking package')}</ColumnHeader>
                                <Table className="border-0 p-0">
                                    <Row>
                                        <CellMinor>{t('Package number')}</CellMinor>
                                        <Cell align="right">
                                            {order.trackingUrl ? (
                                                <ExtendedNextLink href={order.trackingUrl} target="_blank">
                                                    {order.trackingNumber}
                                                </ExtendedNextLink>
                                            ) : (
                                                order.trackingNumber
                                            )}
                                        </Cell>
                                    </Row>
                                </Table>
                            </Cell>
                        )}

                        {!!order.note && (
                            <Cell className="flex-1">
                                <ColumnHeader>{t('Your note')}</ColumnHeader>
                                <Table>
                                    <Row>
                                        <Cell>{order.note}</Cell>
                                    </Row>
                                </Table>
                            </Cell>
                        )}
                        <Cell className="flex-1">
                            <ColumnHeader>{t('Payment information')}</ColumnHeader>
                            <Table className="border-0 p-0">
                                <Row>
                                    <CellMinor>{t('Status')}</CellMinor>
                                    <Cell align="right">
                                        <OrderStatus order={order} />
                                    </Cell>
                                </Row>
                            </Table>
                            {!order.isPaid && order.payment.type === PaymentTypeEnum.GoPay && (
                                <PaymentsInOrderSelect
                                    orderUuid={order.uuid}
                                    paymentTransactionCount={order.paymentTransactionsCount}
                                    withRedirectAfterChanging={false}
                                />
                            )}
                        </Cell>
                    </Row>
                </Table>

                <Table className="mt-10">
                    <Row className="flex flex-col md:flex-row">
                        <Cell className="flex-1">
                            <ColumnHeader>{t('Billing address')}</ColumnHeader>

                            <Table className="border-0 p-0">
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
                        </Cell>

                        <Cell className="flex-1">
                            <ColumnHeader>{t('Delivery address')}</ColumnHeader>

                            <Table className="border-0 p-0">
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
                        </Cell>
                    </Row>
                </Table>

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
                                <Cell className="w-full text-right" colSpan={6}>
                                    <b>
                                        {t('Total price including VAT')}: {formatPrice(order.totalPrice.priceWithVat)}
                                    </b>
                                </Cell>
                            </Row>
                        </Table>
                    </div>
                )}
            </Webline>
        </>
    );
};

const ColumnHeader: FC = ({ children, className }) => (
    <div className={twMergeCustom('border-b-2 border-greyLighter p-4 pl-0 text-lg text-dark', className)}>
        {children}
    </div>
);
