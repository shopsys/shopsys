import { CartApiType, CartType } from './types';
import { useMutation, UseMutationResponse } from 'urql';
import { useFetchQuery } from 'hooks/UseFetchQuery';
import { useShopsysSelector } from 'redux/store';

const cartBody = `
    uuid
    items {
        uuid
        quantity
        product {
            slug
            name
            namePrefix
            nameSuffix
            catalogNumber
            isInSale
            flags {
                name
                rgbColor
            }
            images (size: "list") {
                url
                width
                height
            }
            availability {
                name
            }
            price {
                priceWithVat
                priceWithoutVat
                vatAmount
                isPriceFrom
            }
            availableStoresCount
        }
    }
` as const;

export const cartQuery = (cartUuid: string) =>
    `
      query cart{
          cart(uuid: "${cartUuid}") {
              ${cartBody}      
          }
      }
      ` as const;

export function mapCart(data: CartApiType, currencyCode: string): CartType {
    return {
        ...data,
        items: data.items.map((item) => {
            return {
                ...item,
                product: {
                    ...item.product,
                    price: {
                        ...item.product.price,
                        currencyCode: currencyCode,
                    },
                    availability: item.product.availability.name,
                    image: item.product.images.length === 0 ? null : item.product.images[0],
                },
            };
        }),
    };
}

export const getCart = (cartUuid: string): CartType | undefined => {
    const result = useFetchQuery({ query: cartQuery(cartUuid.toString()) });
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (result.data === undefined) {
        return undefined;
    }

    return mapCart(result?.data?.cart, currentDomainConfig.currencyCode);
};

const removeItemFromCartMutation = `mutation ($cartUuid: Uuid! $cartItemUuid: Uuid!) {
      RemoveFromCart(input:{
              cartUuid: $cartUuid
              cartItemUuid: $cartItemUuid
          }){
              ${cartBody}      
          }
      }` as const;

export const useRemoveItemFromCart = (): UseMutationResponse<CartType, { cartUuid: string; cartItemUuid: string }> => {
    return useMutation(removeItemFromCartMutation);
};
