import { MobileBottomNavigation } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigation';

export const MobileBottomLayer: FC = ({ children }) => (
    <div className="vl:static fixed right-0 -bottom-px left-0 z-overlay vl:contents">
        <div className="pointer-events-none vl:static absolute right-0 bottom-full left-0 vl:contents">{children}</div>

        <MobileBottomNavigation />
    </div>
);
