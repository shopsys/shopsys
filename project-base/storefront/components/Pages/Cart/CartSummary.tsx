import { FreeTransportRange } from 'components/Blocks/FreeTransport/FreeTransportRange';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { CartPreview } from 'components/Pages/Cart/CartPreview';

export const CartSummary: FC = () => (
    <div className="vl:mt-8 vl:flex-row vl:justify-between mt-5 flex flex-col gap-8">
        <div className="vl:max-w-[424px] vl:gap-10 flex w-full flex-col gap-6">
            <FreeTransportRange />

            <PromoCode />
        </div>

        <CartPreview />
    </div>
);
