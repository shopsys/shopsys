import { PacketeryContainerStyled } from './PacketeryContainer.style';
import { FC } from 'react';

const PacketeryContainer: FC = () => {
    const testIdentifier = 'pages-order-transportandpayment-packeterycontainer';

    return <PacketeryContainerStyled id="packetery-container" data-testid={testIdentifier}></PacketeryContainerStyled>;
};

export default PacketeryContainer;
