import { Link } from 'components/Basic/Link/Link';
import { Cell, CellHead, CellMinor, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailQuery } from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type PersonalDataDetailContentProps = {
    data: PersonalDataDetailQuery;
};

export const PersonalDataDetailContent: FC<PersonalDataDetailContentProps> = ({ data }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDate } = useFormatDate();

    const userData = data.accessPersonalData.customerUser;
    const orders = data.accessPersonalData.orders;
    const newsLetterSubscriber = data.accessPersonalData.newsletterSubscriber;
    const exportLink = data.accessPersonalData.exportLink;

    return (
        <>
            <div className="text-center">
                <h1 className="mb-3">{t('Personal data')}</h1>
                <Link isButton href={exportLink} size="small">
                    {t('Download as XML')}
                </Link>
            </div>

            {!!userData && (
                <Webline className="mt-6">
                    <div className="h2 mt-6 mb-3">{t('Billing address')}</div>
                    <Table>
                        <Row className="flex flex-col md:flex-row">
                            <Cell className="flex-1">
                                <Table className="border-0 p-0">
                                    {!!userData.firstName && (
                                        <>
                                            <Row>
                                                <CellMinor>{t('First name')}:</CellMinor>
                                                <Cell>{userData.firstName}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}:</CellMinor>
                                                <Cell>{userData.lastName}</Cell>
                                            </Row>
                                        </>
                                    )}
                                    <Row>
                                        <CellMinor>{t('Email')}</CellMinor>
                                        <Cell>{userData.email}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Phone')}</CellMinor>
                                        <Cell>{userData.telephone}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Street and house no.')}</CellMinor>
                                        <Cell>{userData.street}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('City')}</CellMinor>
                                        <Cell>{userData.city}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Postcode')}</CellMinor>
                                        <Cell>{userData.postcode}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Country')}</CellMinor>
                                        <Cell>{userData.country.name}</Cell>
                                    </Row>
                                </Table>
                            </Cell>

                            {userData.__typename === 'CompanyCustomerUser' && (
                                <Cell className="flex-1">
                                    <Table className="border-0 p-0">
                                        <Row>
                                            <CellMinor>{t('Company name')}</CellMinor>
                                            <Cell>{userData.companyName}</Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Company number')}</CellMinor>
                                            <Cell>{userData.companyNumber}</Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Tax number')}</CellMinor>
                                            <Cell>{userData.companyTaxNumber}</Cell>
                                        </Row>
                                    </Table>
                                </Cell>
                            )}
                        </Row>
                    </Table>

                    {userData.deliveryAddresses.length > 0 && (
                        <>
                            <div className="h2 mt-6 mb-3">{t('Delivery addresses')}</div>
                            <Table>
                                {userData.deliveryAddresses.map((address) => (
                                    <Row key={address.uuid}>
                                        <Cell>
                                            {address.firstName} {address.lastName}
                                            {address.companyName ? ` (${address.companyName})` : ''}, {address.street},{' '}
                                            {address.postcode} {address.city}, {address.country?.name}
                                            {address.telephone ? `, ${t('Phone')}: ${address.telephone}` : ''}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </>
                    )}
                </Webline>
            )}

            <Webline className="mt-6">
                <h2 className="mb-3">{t('My orders')}</h2>

                {orders.length ? (
                    <div className="flex flex-col gap-4">
                        {orders.map((order) => (
                            <Table key={order.uuid}>
                                <Row className="flex flex-col md:flex-row">
                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Order number')}</CellMinor>
                                                <Cell>{order.number}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Creation date')}</CellMinor>
                                                <Cell>{formatDate(order.creationDate, 'l')}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('First name')}</CellMinor>
                                                <Cell>{order.firstName}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}</CellMinor>
                                                <Cell>{order.lastName}</Cell>
                                            </Row>
                                            {!!order.companyName && (
                                                <Row>
                                                    <CellMinor>{t('Company')}</CellMinor>
                                                    <Cell>{order.companyName}</Cell>
                                                </Row>
                                            )}
                                            {!!order.companyNumber && (
                                                <Row>
                                                    <CellMinor>{t('Company number')}</CellMinor>
                                                    <Cell>{order.companyNumber}</Cell>
                                                </Row>
                                            )}
                                            {!!order.companyTaxNumber && (
                                                <Row>
                                                    <CellMinor>{t('Tax number')}</CellMinor>
                                                    <Cell>{order.companyTaxNumber}</Cell>
                                                </Row>
                                            )}
                                            <Row>
                                                <CellMinor>{t('Phone')}</CellMinor>
                                                <Cell>{order.telephone}</Cell>
                                            </Row>
                                            {!!order.deliveryFirstName && (
                                                <Row>
                                                    <CellMinor>{t('Delivery address')}</CellMinor>
                                                    <Cell>
                                                        {order.deliveryFirstName} {order.deliveryLastName}
                                                        {order.deliveryCompanyName
                                                            ? ` (${order.deliveryCompanyName})`
                                                            : ''}
                                                        , {order.deliveryStreet}, {order.deliveryPostcode}{' '}
                                                        {order.deliveryCity}, {order.deliveryCountry?.name}
                                                        {order.deliveryTelephone
                                                            ? `, ${t('Phone')}: ${order.deliveryTelephone}`
                                                            : ''}
                                                    </Cell>
                                                </Row>
                                            )}
                                        </Table>
                                    </Cell>

                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Number of items')}</CellMinor>
                                                <Cell>
                                                    {order.productItems.reduce((sum, item) => sum + item.quantity, 0)}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Shipping')}</CellMinor>
                                                <Cell>{order.transport.name}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Payment')}</CellMinor>
                                                <Cell>{order.payment.name}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Total price including VAT')}</CellMinor>
                                                <Cell>{formatPrice(parseFloat(order.totalPrice.priceWithVat))}</Cell>
                                            </Row>
                                        </Table>
                                    </Cell>
                                </Row>
                            </Table>
                        ))}
                    </div>
                ) : (
                    <p>{t('You have no orders')}</p>
                )}
            </Webline>

            <Webline className="mt-6">
                {newsLetterSubscriber && (
                    <>
                        <div className="h2 mt-6 mb-3">{t('Newsletter')}</div>
                        <Table
                            head={
                                <Row>
                                    <CellHead>{t('Newsletter subscription')}</CellHead>
                                    <CellHead className="text-right">{t('Subscribed from')}</CellHead>
                                </Row>
                            }
                        >
                            <Row>
                                <CellMinor>{newsLetterSubscriber.email}</CellMinor>
                                <td className="text-right">{formatDate(newsLetterSubscriber.createdAt, 'l')}</td>
                            </Row>
                        </Table>
                    </>
                )}
            </Webline>
        </>
    );
};
