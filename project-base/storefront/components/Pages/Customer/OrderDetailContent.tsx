import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Row, Cell, CellHead, Table, CellMinor } from 'components/Basic/Table/Table';
import { Button } from 'components/Forms/Button/Button';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, OrderDetailFragmentApi } from 'graphql/generated';
import { useAddOrderItemsToCart } from 'hooks/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'helpers/twMerge';
import dynamic from 'next/dynamic';

const NotAddedProductsPopup = dynamic(() =>
    import('./NotAddedProductsPopup').then((component) => component.NotAddedProductsPopup),
);
const MergeCartsPopup = dynamic(() => import('./MergeCartsPopup').then((component) => component.MergeCartsPopup));

type OrderDetailContentProps = {
    order: OrderDetailFragmentApi;
    breadcrumbs: BreadcrumbFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-customer-orderdetail-';

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order, breadcrumbs }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const {
        orderForPrefillingUuid,
        setOrderForPrefillingUuid,
        addOrderItemsToEmptyCart,
        mergeOrderItemsWithCurrentCart,
        notAddedProductNames,
        setNotAddedProductNames,
    } = useAddOrderItemsToCart();

    return (
        <>
            <Webline className="mb-2">
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />

                <div className="flex flex-col items-center justify-between lg:mb-4 lg:flex-row">
                    <div className="w-1/5" />
                    <Heading type="h1" className="lg:mb-0">
                        {t('Order number')} {order.number}
                    </Heading>
                    <div className="flex items-center justify-end lg:w-1/5">
                        <Button onClick={() => addOrderItemsToEmptyCart(order.uuid)} className="lg:px-2 lg:py-1">
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
                                    <Cell align="right" dataTestId={TEST_IDENTIFIER + 'creationDate'}>
                                        {formatDateAndTime(order.creationDate)}
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
                                        <Cell align="right" dataTestId={TEST_IDENTIFIER + 'trackingUrl'}>
                                            {order.trackingUrl ? (
                                                <ExtendedNextLink
                                                    href={order.trackingUrl}
                                                    type="static"
                                                    target="_blank"
                                                >
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
                                        <Cell dataTestId={TEST_IDENTIFIER + 'note'}>{order.note}</Cell>
                                    </Row>
                                </Table>
                            </Cell>
                        )}
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
                                            <Cell dataTestId={TEST_IDENTIFIER + 'companyName'}>
                                                {order.companyName}
                                            </Cell>
                                        </Row>

                                        <Row>
                                            <CellMinor>{t('Company number')}</CellMinor>
                                            <Cell dataTestId={TEST_IDENTIFIER + 'companyNumber'}>
                                                {order.companyNumber}
                                            </Cell>
                                        </Row>

                                        {!!order.companyTaxNumber && (
                                            <Row>
                                                <CellMinor>{t('Tax number')}</CellMinor>
                                                <Cell dataTestId={TEST_IDENTIFIER + 'companyTaxNumber'}>
                                                    {order.companyTaxNumber}
                                                </Cell>
                                            </Row>
                                        )}
                                    </>
                                )}

                                {!!order.firstName && (
                                    <>
                                        <Row>
                                            <CellMinor>{t('First name')}</CellMinor>
                                            <Cell dataTestId={TEST_IDENTIFIER + 'firstName'}>{order.firstName}</Cell>
                                        </Row>

                                        <Row>
                                            <CellMinor>{t('Last name')}</CellMinor>
                                            <Cell dataTestId={TEST_IDENTIFIER + 'lastName'}>{order.lastName}</Cell>
                                        </Row>
                                    </>
                                )}

                                <Row>
                                    <CellMinor>{t('Email')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'email'}>{order.email}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Phone')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'telephone'}>{order.telephone}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Street and house no.')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'street'}>{order.street}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('City')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'city'}>{order.city}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Postcode')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'postcode'}>{order.postcode}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Country')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'country'}>{order.country.name}</Cell>
                                </Row>
                            </Table>
                        </Cell>

                        <Cell className="flex-1">
                            <ColumnHeader>{t('Delivery address')}</ColumnHeader>

                            <Table className="border-0 p-0">
                                <Row>
                                    <CellMinor>{t('First name')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryFirstName'}>
                                        {order.deliveryFirstName}
                                    </Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('First name')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryFirstName'}>
                                        {order.deliveryFirstName}
                                    </Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Last name')}</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryLastName'}>
                                        {order.deliveryLastName}
                                    </Cell>
                                </Row>

                                {!!order.deliveryCompanyName && (
                                    <Row>
                                        <CellMinor>{t('Company name')}</CellMinor>
                                        <Cell dataTestId={TEST_IDENTIFIER + 'deliveryCompanyName'}>
                                            {order.deliveryCompanyName}
                                        </Cell>
                                    </Row>
                                )}

                                <Row>
                                    <CellMinor>{t('Phone')}:</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryTelephone'}>
                                        {order.deliveryTelephone}
                                    </Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Street and house no.')}:</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryStreet'}>{order.deliveryStreet}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('City')}:</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryCity'}>{order.deliveryCity}</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Postcode')}:</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryPostcode'}>
                                        {order.deliveryPostcode}
                                    </Cell>
                                </Row>

                                <Row>
                                    <CellMinor>{t('Country')}:</CellMinor>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'deliveryCountry'}>
                                        {order.deliveryCountry?.name}
                                    </Cell>
                                </Row>
                            </Table>
                        </Cell>
                    </Row>
                </Table>

                {!!order.items.length && (
                    <div className="mt-10">
                        <Heading type="h2" className="text-center">
                            {t('Your purchase')}
                        </Heading>

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
                                <Row key={index} dataTestId={TEST_IDENTIFIER + 'item-' + index}>
                                    <Cell dataTestId={TEST_IDENTIFIER + 'item-name'}>{item.name}</Cell>
                                    <Cell className="text-right" dataTestId={TEST_IDENTIFIER + 'item-unitprice'}>
                                        {formatPrice(item.unitPrice.priceWithVat)}
                                    </Cell>
                                    <Cell className="text-right" dataTestId={TEST_IDENTIFIER + 'item-quantity'}>
                                        {item.quantity} {item.unit}
                                    </Cell>
                                    <Cell className="nowrap text-right" dataTestId={TEST_IDENTIFIER + 'item-vat'}>
                                        {parseFloat(item.vatRate)} %
                                    </Cell>
                                    <Cell className="text-right" dataTestId={TEST_IDENTIFIER + 'item-price'}>
                                        {formatPrice(item.totalPrice.priceWithoutVat)}
                                    </Cell>
                                    <Cell className="text-right" dataTestId={TEST_IDENTIFIER + 'item-pricevat'}>
                                        {formatPrice(item.totalPrice.priceWithVat)}
                                    </Cell>
                                </Row>
                            ))}
                            <Row>
                                <Cell
                                    colSpan={6}
                                    className="w-full text-right"
                                    dataTestId={TEST_IDENTIFIER + 'ordet-total-pricevat'}
                                >
                                    <b>
                                        {t('Total price including VAT')}: {formatPrice(order.totalPrice.priceWithVat)}
                                    </b>
                                </Cell>
                            </Row>
                        </Table>
                    </div>
                )}
            </Webline>

            {!!orderForPrefillingUuid && (
                <MergeCartsPopup
                    mergeOrderItemsWithCurrentCart={mergeOrderItemsWithCurrentCart}
                    orderForPrefillingUuid={orderForPrefillingUuid}
                    onCloseCallback={() => setOrderForPrefillingUuid(undefined)}
                />
            )}

            {!!notAddedProductNames?.length && (
                <NotAddedProductsPopup
                    notAddedProductNames={notAddedProductNames}
                    onCloseCallback={() => setNotAddedProductNames(undefined)}
                />
            )}
        </>
    );
};

const ColumnHeader: FC = ({ children, className }) => (
    <div className={twMergeCustom('border-b-2 border-greyLighter p-4 pl-0 text-lg text-dark', className)}>
        {children}
    </div>
);
