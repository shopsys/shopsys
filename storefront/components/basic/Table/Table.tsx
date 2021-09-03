import { FC, ReactNode } from 'react';
import { TableStyled } from './Table.style';

/**
 * Wrapping element for html table - it gives table styling.
 */
type nativeProps = {
    children: ReactNode;
};

const Table: FC<nativeProps> = (props) => {
    return <TableStyled>{props.children}</TableStyled>;
};

/* @component */
export default Table;
