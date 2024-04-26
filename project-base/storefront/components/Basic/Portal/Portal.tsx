import { useSessionStore } from 'store/useSessionStore';

export const Portal: FC = () => {
    const portalContent = useSessionStore((s) => s.portalContent);

    return portalContent;
};
