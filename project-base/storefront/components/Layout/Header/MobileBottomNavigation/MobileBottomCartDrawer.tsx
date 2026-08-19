import { Drawer } from 'components/Basic/Drawer/Drawer';
import { CartInHeaderList } from 'components/Layout/Header/Cart/CartInHeaderList';

type MobileBottomCartDrawerProps = {
    isActive: boolean;
    setIsActive: (isActive: boolean) => void;
    title: string;
};

export const MobileBottomCartDrawer: FC<MobileBottomCartDrawerProps> = ({ isActive, setIsActive, title }) => (
    <Drawer className="flex flex-col overflow-hidden" isActive={isActive} setIsActive={setIsActive} title={title}>
        <CartInHeaderList hideFocusTrap isDrawer />
    </Drawer>
);
