import FreeTransportRange from 'components/Blocks/FreeTransport/FreeTransportRange';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { CartPreview } from 'components/Pages/Cart/CartPreview';

export const CartSummary: FC = () => (
    <div className="mb-8 flex flex-col lg:flex-row vl:items-baseline flex-wrap gap-2 lg:gap-0">
        <div className="pr-0 vl:pr-4 lg:w-1/2 vl:w-4/12">
            <PromoCode />
        </div>
        <div className="ml-auto flex justify-end items-center vl:pr-8 lg:w-1/2 vl:w-5/12">
            <FreeTransportRange />
        </div>
        <div className="lg:w-full vl:w-3/12">
            <CartPreview />
        </div>
    </div>
);
