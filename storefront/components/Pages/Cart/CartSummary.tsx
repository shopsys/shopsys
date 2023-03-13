import { FreeTransport } from 'components/Blocks/FreeTransport/FreeTransport';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartPreview } from 'components/Pages/Cart/CartPreview';

export const CartSummary: FC = () => (
    <Webline>
        <div className="mb-8 flex flex-col vl:flex-row vl:items-baseline">
            <div className="pr-8 vl:pr-4">
                <PromoCode />
            </div>
            <div className="ml-auto text-center vl:pr-8">
                <FreeTransport />
            </div>
            <div className="vl:w-80">
                <CartPreview />
            </div>
        </div>
    </Webline>
);
