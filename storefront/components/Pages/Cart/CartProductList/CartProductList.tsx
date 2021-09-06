import CartProductListItem from './CartProductListItem';
import { FC } from 'react';
import { ProductCartItemType } from 'connectors/cart/types';
import { StyledCartProductList } from './CartProductList.style';
import Webline from 'components/layout/Webline';

const cart = {
    uuid: 'aaksldmakdm',
    items: [
        {
            uuid: 'asdjkasdnn',
            quantity: 2,
            product: {
                slug: '/54-philips-crt-32pfl4308',
                name: '54" Philips CRT 32PFL4308',
                namePrefix: 'fakt cool',
                nameSuffix: 'cerna',
                catalogNumber: 'akdemasdea',
                isInSale: true,
                flags: [
                    {
                        name: 'sleva',
                        rgbColor: '#ff0000',
                    },
                ],
                image: {
                    height: 50,
                    width: 75,
                    url: 'https://master.ssfwcc.ci.shopsys.cloud/content/images/product/thumbnail/canon-mg3550_9.jpg',
                },
                price: {
                    currencyCode: 'CZK',
                    isPriceFrom: false,
                    priceWithVat: 1000,
                    priceWithoutVat: 500,
                    vatAmount: 500,
                },
                availability: 'skladem',
                availableStoresCount: 0,
            } as ProductCartItemType,
        },
        {
            uuid: 'asdjkasasdnn',
            quantity: 1,
            product: {
                slug: '/54-samsung-crt-32pfl4308',
                name: '54" Samsung CRT 32PFL4308',
                namePrefix: 'fakt cool',
                nameSuffix: 'bila',
                catalogNumber: 'akdemasdea',
                isInSale: false,
                flags: [
                    {
                        name: 'sleva',
                        rgbColor: '#ff0000',
                    },
                ],
                image: {
                    height: 50,
                    width: 75,
                    url: 'https://master.ssfwcc.ci.shopsys.cloud/content/images/product/thumbnail/canon-mg3550_9.jpg',
                },
                price: {
                    currencyCode: 'CZK',
                    isPriceFrom: false,
                    priceWithVat: 1000,
                    priceWithoutVat: 500,
                    vatAmount: 500,
                },
                availability: 'skladem',
                availableStoresCount: 3,
            } as ProductCartItemType,
        },
    ],
};

const CartProductList: FC = () => {
    return (
        <Webline>
            <StyledCartProductList>
                {cart.items.map((item) => (
                    <CartProductListItem key={item.uuid} item={item} />
                ))}
            </StyledCartProductList>
        </Webline>
    );
};

export default CartProductList;
