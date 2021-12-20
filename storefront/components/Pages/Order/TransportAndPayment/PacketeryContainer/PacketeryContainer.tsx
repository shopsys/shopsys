import { FC } from 'react';
import { PacketeryContainerStyled } from './PacketeryContainer.style';

const PacketeryContainer: FC = () => {
    const testIdentifier = 'pages-order-transportandpayment-packeterycontainer';

    return <PacketeryContainerStyled id="packetery-container" data-testid={testIdentifier}></PacketeryContainerStyled>;
};

export default PacketeryContainer;
