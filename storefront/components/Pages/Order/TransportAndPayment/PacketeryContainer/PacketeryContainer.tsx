import { PacketeryContainerStyled } from './PacketeryContainer.style';
import { FC } from 'react';

export const PacketeryContainer: FC = () => {
    const testIdentifier = 'pages-order-transportandpayment-packeterycontainer';

    return <PacketeryContainerStyled id="packetery-container" data-testid={testIdentifier}></PacketeryContainerStyled>;
};
