import { useRef } from 'react';
import { createPortal } from 'react-dom';

export const Portal: FC = ({ children }) => {
    const portalElementRef = useRef(document.getElementById('portal'));
    const hasMounted = useRef(false);

    if (portalElementRef.current === null) {
        return null;
    }

    if (!hasMounted.current) {
        portalElementRef.current.innerHTML = '';
        hasMounted.current = true;
    }

    return createPortal(children, portalElementRef.current);
};
