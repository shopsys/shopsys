import { useRef } from 'react';
import { createPortal } from 'react-dom';

export const Portal: FC = ({ children }) => {
    const portalElementRef = useRef(document.getElementById('portal'));

    if (portalElementRef.current === null) {
        return null;
    }

    return createPortal(children, portalElementRef.current);
};
