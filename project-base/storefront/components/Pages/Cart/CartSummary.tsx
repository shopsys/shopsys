import { FreeTransport } from 'components/Blocks/FreeTransport/FreeTransport';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { CartPreview } from 'components/Pages/Cart/CartPreview';

export const CartSummary: FC = () => (
    <div className="mb-8 flex flex-col vl:flex-row vl:items-baseline">
        <div className="pr-0 vl:pr-4 w-1/3">
            <PromoCode />
        </div>
        <div className="ml-auto text-center vl:pr-8 w-1/3">
            <FreeTransport />
        </div>
        <div className="w-1/3 vl:w-80">
            <CartPreview />
        </div>
    </div>
);
