import { TableStyled } from './Table.style';
import { FC } from 'react';

const TEST_IDENTIFIER = 'basic-table';

export const Table: FC = ({ children }) => <TableStyled data-testid={TEST_IDENTIFIER}>{children}</TableStyled>;
