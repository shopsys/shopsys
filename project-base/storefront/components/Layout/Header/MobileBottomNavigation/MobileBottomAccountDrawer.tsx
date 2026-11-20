import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';

type MobileBottomAccountDrawerProps = {
    isActive: boolean;
    setIsActive: (isActive: boolean) => void;
    title: string;
};

export const MobileBottomAccountDrawer: FC<MobileBottomAccountDrawerProps> = ({ isActive, setIsActive, title }) => (
    <Drawer isActive={isActive} setIsActive={setIsActive} title={title}>
        <UserMenu hideFocusTrap onMenuClose={() => setIsActive(false)} />
    </Drawer>
);
