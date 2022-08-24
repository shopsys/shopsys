import { FC, useRef } from 'react';
import { createPortal } from 'react-dom';

export const Portal: FC = (props) => {
    const portalElementRef = useRef(document.getElementById('portal'));

    if (portalElementRef.current === null) {
        return null;
    }

    return createPortal(props.children, portalElementRef.current);
};
