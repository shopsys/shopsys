import { FreeTransportRange } from 'components/Blocks/FreeTransport/FreeTransportRange';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { CartPreview } from 'components/Pages/Cart/CartPreview';
import { RefObject } from 'react';

type CartSummaryProps = {
    cartPreviewRef?: RefObject<HTMLDivElement | null>;
};

export const CartSummary: FC<CartSummaryProps> = ({ cartPreviewRef }) => (
    <div className="mt-5 vl:mt-8 flex vl:flex-row flex-col vl:justify-between gap-8">
        <div className="flex w-full vl:max-w-[424px] flex-col gap-6 vl:gap-10">
            <FreeTransportRange />

            <PromoCode />
        </div>

        <CartPreview isFirstStep wrapperRef={cartPreviewRef} />
    </div>
);
