import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useSetCartItemAdditionalServicesMutation } from 'graphql/requests/cart/mutations/SetCartItemAdditionalServicesMutation.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { usePersistStoreApi } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

type GtmToggleContext = {
    cartItem: TypeCartItemFragment;
    toggledService: TypeAdditionalServiceFragment;
    isServiceAdded: boolean;
};

const settingCartItemUuids = new Set<string>();

export const useSetCartItemAdditionalServices = (gtmProductListName: GtmProductListNameType) => {
    const [{ fetching: isSettingAdditionalServices }, setCartItemAdditionalServicesMutation] =
        useSetCartItemAdditionalServicesMutation();
    const domainConfig = useDomainConfig();
    const persistStoreApi = usePersistStoreApi();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { fetchCart } = useCurrentCart();
    const { canSeePrices } = useAuthorization();

    const setCartItemAdditionalServices = async (
        cartItemUuid: string,
        additionalServiceUuids: string[],
        gtmToggleContext?: GtmToggleContext,
    ) => {
        if (settingCartItemUuids.has(cartItemUuid)) {
            return null;
        }

        settingCartItemUuids.add(cartItemUuid);

        try {
            const cartUuid = persistStoreApi.getState().cartUuid;
            const setCartItemAdditionalServicesResult = await setCartItemAdditionalServicesMutation({
                input: { cartUuid, cartItemUuid, additionalServiceUuids },
            });

            const updatedCart = setCartItemAdditionalServicesResult.data?.SetCartItemAdditionalServices ?? null;

            if (setCartItemAdditionalServicesResult.error || !updatedCart) {
                fetchCart();

                return null;
            }

            dispatchBroadcastChannel('refetchCart', domainConfig.domainId);

            if (gtmToggleContext) {
                import('gtm/handlers/onGtmChangeCartItemAdditionalServiceEventHandler').then(
                    ({ onGtmChangeCartItemAdditionalServiceEventHandler }) => {
                        onGtmChangeCartItemAdditionalServiceEventHandler(
                            gtmToggleContext.isServiceAdded,
                            gtmToggleContext.toggledService,
                            gtmToggleContext.cartItem,
                            updatedCart,
                            gtmProductListName,
                            domainConfig,
                            isUserLoggedIn,
                            !canSeePrices,
                        );
                    },
                );
            }

            return updatedCart;
        } finally {
            settingCartItemUuids.delete(cartItemUuid);
        }
    };

    return { setCartItemAdditionalServices, isSettingAdditionalServices };
};
