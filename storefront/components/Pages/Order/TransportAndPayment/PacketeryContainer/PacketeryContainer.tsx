import { PacketeryContainerStyled } from './PacketeryContainer.style';
import { FC } from 'react';

const TEST_IDENTIFIER = 'pages-order-transportandpayment-packeterycontainer';

export const PacketeryContainer: FC = () => (
    <PacketeryContainerStyled id="packetery-container" data-testid={TEST_IDENTIFIER}></PacketeryContainerStyled>
);
