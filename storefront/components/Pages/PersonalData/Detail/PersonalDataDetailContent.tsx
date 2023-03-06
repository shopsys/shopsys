import { Heading } from 'components/Basic/Heading/Heading';
import { Link } from 'components/Basic/Link/Link';
import { TableGrid, TableGridColumn } from 'components/Basic/TableGrid/TableGrid';
import { TableGridColumns } from 'components/Basic/TableGrid/TableGridElements';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailQueryApi } from 'graphql/generated';
import { formatDate } from 'helpers/formaters/formatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

type PersonalDataDetailContentProps = {
    data: PersonalDataDetailQueryApi;
};

const TEST_IDENTIFIER = 'pages-personal-data-detail-';

export const PersonalDataDetailContent: FC<PersonalDataDetailContentProps> = ({ data }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    const userData = data.accessPersonalData.customerUser ?? null;
    const orders = data.accessPersonalData.orders;
    const newsLetterSubscriber = data.accessPersonalData.newsletterSubscriber ?? null;
    const exportLink = data.accessPersonalData.exportLink;

    return (
        <>
            <Webline style={{ marginBottom: '24px' }}>
                <div className="text-center">
                    <Heading type="h1">{t('Personal data')}</Heading>
                    <Link isButton href={exportLink} size="small">
                        {t('Download as XML')}
                    </Link>
                </div>
            </Webline>
            {userData !== null && (
                <>
                    <Webline>
                        <Heading type="h2">{t('Billing address')}</Heading>
                        <TableGrid>
                            <TableGridColumns>
                                <TableGridColumn>
                                    {userData.firstName !== '' && (
                                        <>
                                            <tr>
                                                <td>{t('First name')}:</td>
                                                <td data-testid={TEST_IDENTIFIER + 'firstName'}>
                                                    {userData.firstName}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{t('Last name')}:</td>
                                                <td data-testid={TEST_IDENTIFIER + 'lastName'}>{userData.lastName}</td>
                                            </tr>
                                        </>
                                    )}
                                    <tr>
                                        <td>{t('Email')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'email'}>{userData.email}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Phone')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'telephone'}>{userData.telephone}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Street and house no.')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'street'}>{userData.street}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('City')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'city'}>{userData.city}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Postcode')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'postcode'}>{userData.postcode}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Country')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'country'}>{userData.country.name}</td>
                                    </tr>
                                </TableGridColumn>
                                {userData.__typename === 'CompanyCustomerUser' && (
                                    <TableGridColumn>
                                        <tr>
                                            <td>{t('Company name')}:</td>
                                            <td data-testid={TEST_IDENTIFIER + 'companyName'}>
                                                {userData.companyName}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{t('Company number')}:</td>
                                            <td data-testid={TEST_IDENTIFIER + 'companyNumber'}>
                                                {userData.companyNumber}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{t('Tax number')}:</td>
                                            <td data-testid={TEST_IDENTIFIER + 'companyTaxNumber'}>
                                                {userData.companyTaxNumber}
                                            </td>
                                        </tr>
                                    </TableGridColumn>
                                )}
                            </TableGridColumns>
                        </TableGrid>
                    </Webline>
                    {userData.deliveryAddresses.length > 0 && (
                        <Webline>
                            <Heading type="h2">{t('Delivery addresses')}</Heading>
                            <TableGrid>
                                {userData.deliveryAddresses.map((address) => (
                                    <tr key={address.uuid}>
                                        <td data-testid={TEST_IDENTIFIER + 'firstName'}>
                                            {address.firstName} {address.lastName}
                                            {address.companyName !== null ? ` (${address.companyName})` : ''},{' '}
                                            {address.street}, {address.postcode} {address.city}, {address.country?.name}
                                            {address.telephone !== null ? `, ${t('Phone')}: ${address.telephone}` : ''}
                                        </td>
                                    </tr>
                                ))}
                            </TableGrid>
                        </Webline>
                    )}
                </>
            )}
            <Webline style={{ marginBottom: '24px' }}>
                <Heading type="h2">{t('My orders')}</Heading>
                {orders.length !== 0 ? (
                    <>
                        {orders.map((order) => (
                            <TableGrid key={order.uuid}>
                                <TableGridColumns>
                                    <TableGridColumn>
                                        <tr>
                                            <td>{t('Order number')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'number'}>{order.number}</td>
                                        </tr>
                                        <tr>
                                            <td>{t('Creation date')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'creation-date'}>
                                                {formatDate(order.creationDate, 'l')}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{t('First name')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'firstName'}>{order.firstName}</td>
                                        </tr>
                                        <tr>
                                            <td>{t('Last name')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'lastName'}>{order.lastName}</td>
                                        </tr>
                                        {order.companyName !== null && (
                                            <tr>
                                                <td>{t('Company')}</td>
                                                <td data-testid={TEST_IDENTIFIER + 'companyName'}>
                                                    {order.companyName}
                                                </td>
                                            </tr>
                                        )}
                                        {order.companyNumber !== null && (
                                            <tr>
                                                <td>{t('Company number')}</td>
                                                <td data-testid={TEST_IDENTIFIER + 'companyNumber'}>
                                                    {order.companyNumber}
                                                </td>
                                            </tr>
                                        )}
                                        {order.companyTaxNumber !== null && (
                                            <tr>
                                                <td>{t('Tax number')}</td>
                                                <td data-testid={TEST_IDENTIFIER + 'taxNumber'}>
                                                    {order.companyTaxNumber}
                                                </td>
                                            </tr>
                                        )}
                                        <tr>
                                            <td>{t('Phone')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'telephone'}>{order.telephone}</td>
                                        </tr>
                                        {order.deliveryFirstName !== null && (
                                            <tr>
                                                <td>{t('Delivery address')}</td>
                                                <td data-testid={TEST_IDENTIFIER + 'deliveryAddress'}>
                                                    {order.deliveryFirstName} {order.deliveryLastName}
                                                    {order.deliveryCompanyName !== null
                                                        ? ` (${order.deliveryCompanyName})`
                                                        : ''}
                                                    , {order.deliveryStreet}, {order.deliveryPostcode}{' '}
                                                    {order.deliveryCity}, {order.deliveryCountry?.name}
                                                    {order.deliveryTelephone !== null
                                                        ? `, ${t('Phone')}: ${order.deliveryTelephone}`
                                                        : ''}
                                                </td>
                                            </tr>
                                        )}
                                    </TableGridColumn>
                                    <TableGridColumn>
                                        <tr>
                                            <td>{t('Number of items')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'quantity'}>
                                                {order.productItems.reduce((sum, item) => sum + item.quantity, 0)}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{t('Shipping')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'transport'}>{order.transport.name}</td>
                                        </tr>
                                        <tr>
                                            <td>{t('Payment')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'payment'}>{order.payment.name}</td>
                                        </tr>
                                        <tr>
                                            <td>{t('Total price including VAT')}</td>
                                            <td data-testid={TEST_IDENTIFIER + 'total-price'}>
                                                {formatPrice(parseFloat(order.totalPrice.priceWithVat))}
                                            </td>
                                        </tr>
                                    </TableGridColumn>
                                </TableGridColumns>
                            </TableGrid>
                        ))}
                    </>
                ) : (
                    <p>{t('You have no orders')}</p>
                )}
            </Webline>
            {newsLetterSubscriber !== null && (
                <Webline>
                    <Heading type="h2">{t('Newsletter')}</Heading>
                    <TableGrid>
                        <tr>
                            <th>{t('Newsletter subscription')}</th>
                            <th className="text-right">{t('Subscribed from')}</th>
                        </tr>
                        <tr>
                            <td>{newsLetterSubscriber.email}</td>
                            <td className="text-right">{formatDate(newsLetterSubscriber.createdAt, 'l')}</td>
                        </tr>
                    </TableGrid>
                </Webline>
            )}
        </>
    );
};
