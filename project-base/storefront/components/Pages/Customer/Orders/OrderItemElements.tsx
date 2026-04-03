import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { ReactNode } from 'react';

type ElementWithImageProps = {
    image: string | undefined;
    name: string;
};

export const ElementWithImage: FC<ElementWithImageProps> = ({ image, name }) => {
    return (
        <div className="flex items-center gap-4 font-secondary font-semibold">
            <div
                className="flex h-12 w-20 shrink-0 items-center justify-center rounded-xl bg-background-default"
                data-tid={TIDs.order_list_transport_and_payment_image}
            >
                <Image
                    alt={name}
                    className="aspect-video h-7 object-contain object-center mix-blend-multiply"
                    height={28}
                    src={image}
                    width={60}
                />
            </div>

            {name}
        </div>
    );
};

type OrderItemColumnInfoProps = {
    title: string;
    children: ReactNode;
    tid?: string;
};

export const OrderItemColumnInfo: FC<OrderItemColumnInfoProps> = ({ title, children, tid }) => {
    return (
        <div className="flex min-w-[100px] flex-col gap-1 font-secondary font-semibold text-sm">
            <span className="text-text-less">{title}</span>
            <span data-tid={tid}>{children}</span>
        </div>
    );
};

type OrderItemRowInfoProps = {
    title: string;
    children: ReactNode;
};

export const OrderItemRowInfo: FC<OrderItemRowInfoProps> = ({ title, children }) => {
    return (
        <div className="flex vl:flex-row flex-col vl:items-center gap-1 vl:gap-3 text-sm">
            <span className="min-w-[100px] font-secondary font-semibold text-text-less">{title}</span>
            {children}
        </div>
    );
};
