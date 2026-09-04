import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';

const DeliveryOptionsPopup = dynamic(
    () => import('./DeliveryOptionsPopup').then((component) => component.DeliveryOptionsPopup),
    { ssr: false },
);

export const useOpenDeliveryOptionsPopup = () => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    return (products: DeliveryOptionsProduct[], preselectedProductUuid?: string) => {
        storeCurrentFocus();
        updatePortalContent(
            <DeliveryOptionsPopup preselectedProductUuid={preselectedProductUuid} products={products} />,
        );
    };
};
