import { useSessionStore } from 'store/useSessionStore';
import { OVERLAY_PORTAL_ROOT_ID } from './portalConstants';

export const Portal: FC = () => {
    const portalContent = useSessionStore((s) => s.portalContent);

    return (
        <>
            {portalContent}
            <div id={OVERLAY_PORTAL_ROOT_ID} />
        </>
    );
};
