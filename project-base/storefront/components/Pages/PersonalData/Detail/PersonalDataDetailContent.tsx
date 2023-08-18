import { Heading } from 'components/Basic/Heading/Heading';
import { Link } from 'components/Basic/Link/Link';
import { Cell, CellHead, CellMinor, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailQueryApi } from 'graphql/generated';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type PersonalDataDetailContentProps = {
    data: PersonalDataDetailQueryApi;
};

const TEST_IDENTIFIER = 'pages-personal-data-detail-';

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
                <Heading type="h1">{t('Personal data')}</Heading>
                <Link isButton href={exportLink} size="small">
                    {t('Download as XML')}
                </Link>
            </div>

            {!!userData && (
                <Webline className="mt-6">
                    <Heading type="h2" className="mt-6">
                        {t('Billing address')}
                    </Heading>
                    <Table>
                        <Row className="flex flex-col md:flex-row">
                            <Cell className="flex-1">
                                <Table className="border-0 p-0">
                                    {!!userData.firstName && (
                                        <>
                                            <Row>
                                                <CellMinor>{t('First name')}:</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'firstName'}>
                                                    {userData.firstName}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}:</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'lastName'}>
                                                    {userData.lastName}
                                                </Cell>
                                            </Row>
                                        </>
                                    )}
                                    <Row>
                                        <CellMinor>{t('Email')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'email'}>{userData.email}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Phone')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'telephone'}>{userData.telephone}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Street and house no.')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'street'}>{userData.street}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('City')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'city'}>{userData.city}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Postcode')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'postcode'}>{userData.postcode}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Country')}</CellMinor>
                                        <Cell data-testid={TEST_IDENTIFIER + 'country'}>{userData.country.name}</Cell>
                                    </Row>
                                </Table>
                            </Cell>

                            {userData.__typename === 'CompanyCustomerUser' && (
                                <Cell className="flex-1">
                                    <Table className="border-0 p-0">
                                        <Row>
                                            <CellMinor>{t('Company name')}</CellMinor>
                                            <Cell data-testid={TEST_IDENTIFIER + 'companyName'}>
                                                {userData.companyName}
                                            </Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Company number')}</CellMinor>
                                            <Cell data-testid={TEST_IDENTIFIER + 'companyNumber'}>
                                                {userData.companyNumber}
                                            </Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Tax number')}</CellMinor>
                                            <Cell data-testid={TEST_IDENTIFIER + 'companyTaxNumber'}>
                                                {userData.companyTaxNumber}
                                            </Cell>
                                        </Row>
                                    </Table>
                                </Cell>
                            )}
                        </Row>
                    </Table>

                    {userData.deliveryAddresses.length > 0 && (
                        <>
                            <Heading type="h2" className="mt-6">
                                {t('Delivery addresses')}
                            </Heading>
                            <Table>
                                {userData.deliveryAddresses.map((address) => (
                                    <Row key={address.uuid}>
                                        <Cell data-testid={TEST_IDENTIFIER + 'firstName'}>
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
                <Heading type="h2">{t('My orders')}</Heading>

                {orders.length ? (
                    <div className="flex flex-col gap-4">
                        {orders.map((order) => (
                            <Table key={order.uuid}>
                                <Row className="flex flex-col md:flex-row">
                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Order number')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'number'}>{order.number}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Creation date')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'creation-date'}>
                                                    {formatDate(order.creationDate, 'l')}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('First name')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'firstName'}>
                                                    {order.firstName}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'lastName'}>{order.lastName}</Cell>
                                            </Row>
                                            {!!order.companyName && (
                                                <Row>
                                                    <CellMinor>{t('Company')}</CellMinor>
                                                    <Cell data-testid={TEST_IDENTIFIER + 'companyName'}>
                                                        {order.companyName}
                                                    </Cell>
                                                </Row>
                                            )}
                                            {!!order.companyNumber && (
                                                <Row>
                                                    <CellMinor>{t('Company number')}</CellMinor>
                                                    <Cell data-testid={TEST_IDENTIFIER + 'companyNumber'}>
                                                        {order.companyNumber}
                                                    </Cell>
                                                </Row>
                                            )}
                                            {!!order.companyTaxNumber && (
                                                <Row>
                                                    <CellMinor>{t('Tax number')}</CellMinor>
                                                    <Cell data-testid={TEST_IDENTIFIER + 'taxNumber'}>
                                                        {order.companyTaxNumber}
                                                    </Cell>
                                                </Row>
                                            )}
                                            <Row>
                                                <CellMinor>{t('Phone')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'telephone'}>
                                                    {order.telephone}
                                                </Cell>
                                            </Row>
                                            {!!order.deliveryFirstName && (
                                                <Row>
                                                    <CellMinor>{t('Delivery address')}</CellMinor>
                                                    <Cell data-testid={TEST_IDENTIFIER + 'deliveryAddress'}>
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
                                                <Cell data-testid={TEST_IDENTIFIER + 'quantity'}>
                                                    {order.productItems.reduce((sum, item) => sum + item.quantity, 0)}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Shipping')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'transport'}>
                                                    {order.transport.name}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Payment')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'payment'}>
                                                    {order.payment.name}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Total price including VAT')}</CellMinor>
                                                <Cell data-testid={TEST_IDENTIFIER + 'total-price'}>
                                                    {formatPrice(parseFloat(order.totalPrice.priceWithVat))}
                                                </Cell>
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
                        <Heading type="h2" className="mt-6">
                            {t('Newsletter')}
                        </Heading>
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
