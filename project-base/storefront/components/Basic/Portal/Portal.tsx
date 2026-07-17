import { useSessionStore } from 'store/useSessionStore';

export const OVERLAY_PORTAL_ROOT_ID = 'overlay-portal-root';

export const Portal: FC = () => {
    const portalContent = useSessionStore((s) => s.portalContent);

    return (
        <>
            {portalContent}
            <div id={OVERLAY_PORTAL_ROOT_ID} />
        </>
    );
};
