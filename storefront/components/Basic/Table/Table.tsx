import { TableStyled } from './Table.style';
import { FC } from 'react';

export const Table: FC = (props) => {
    const testIdentifier = 'basic-table';

    return <TableStyled data-testid={testIdentifier}>{props.children}</TableStyled>;
};
