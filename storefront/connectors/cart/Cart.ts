import { AddToCartResultType, CartApiType, CartType } from './types';
import { useMutation, UseMutationResponse } from 'urql';
import { mapProductPriceData } from 'connectors/products/Products';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/store';

const cartBody = `
    uuid
    items {
        uuid
        quantity
        product {
            uuid
            slug
            fullName
            catalogNumber
            stockQuantity
            flags {
                name
                rgbColor
            }
            images (size: "list") {
                url
                width
                height
            }
            stockQuantity
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
            unit {
                name
            }
        }
    }
` as const;

const cartQuery = `
      query ($cartUuid: Uuid){
          cart(uuid: $cartUuid) {
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
                    price: mapProductPriceData(item.product.price, currencyCode),
                    availability: item.product.availability.name,
                    image: item.product.images.length === 0 ? null : item.product.images[0],
                },
            };
        }),
    };
}

export const getCart = (): CartType | undefined => {
    const cartUuid = typeof localStorage !== 'undefined' ? localStorage.getItem('cartUuid') : undefined;
    const result = useFetchQuery({
        query: cartQuery,
        variables: { cartUuid },
        pause: cartUuid === undefined || cartUuid === null,
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    if (result.data === undefined || result.data.cart === null) {
        return undefined;
    }

    return mapCart(result.data.cart, currencyCode);
};

const removeItemFromCartMutation = `mutation ($cartUuid: Uuid! $cartItemUuid: Uuid!) {
      RemoveFromCart(input:{
              cartUuid: $cartUuid
              cartItemUuid: $cartItemUuid
          }){
              ${cartBody}      
          }
      }` as const;

export const useRemoveItemFromCart = (): UseMutationResponse<
    { RemoveFromCart: CartApiType },
    { cartUuid: string; cartItemUuid: string }
> => {
    return useMutation(removeItemFromCartMutation);
};

const changeCartItemQuantityMutation =
    `mutation ($cartUuid: Uuid $productUuid: Uuid! $quantity: Int! $isAbsoluteQuantity: Boolean ) {
        AddToCart(input:{
            cartUuid: $cartUuid
            productUuid: $productUuid
            quantity: $quantity
            isAbsoluteQuantity: $isAbsoluteQuantity
        }){
            ${cartBody}
            addProductResult {
                notOnStockQuantity
                overLimitQuantity
                isNew
                isQuantityOverLimit
                addedQuantity
            }
        }
    }` as const;

export const useChangeCartItemQuantity = (): UseMutationResponse<
    { AddToCart: AddToCartResultType },
    { cartUuid?: string; productUuid: string; quantity: number; isAbsoluteQuantity: boolean }
> => {
    return useMutation(changeCartItemQuantityMutation);
};
