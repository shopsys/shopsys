import FreeTransportRange from 'components/Blocks/FreeTransport/FreeTransportRange';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { CartPreview } from 'components/Pages/Cart/CartPreview';

export const CartSummary: FC = () => (
    <div className="mb-8 flex flex-col flex-wrap gap-2 lg:flex-row lg:gap-0 vl:items-baseline">
        <div className="pr-0 lg:w-1/2 vl:w-4/12 vl:pr-4">
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
