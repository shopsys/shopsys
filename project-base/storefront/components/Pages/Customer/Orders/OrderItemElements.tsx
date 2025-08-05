import { Image } from 'components/Basic/Image/Image';
import { ReactNode } from 'react';

type ElementWithImageProps = {
    image: string | undefined;
    name: string;
};

export const ElementWithImage: FC<ElementWithImageProps> = ({ image, name }) => {
    return (
        <div className="font-secondary flex items-center gap-4 font-semibold">
            <div className="bg-background-default flex h-12 w-20 shrink-0 items-center justify-center rounded-xl">
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
};

export const OrderItemColumnInfo: FC<OrderItemColumnInfoProps> = ({ title, children }) => {
    return (
        <div className="font-secondary flex min-w-[100px] flex-col gap-1 text-sm font-semibold">
            <span className="text-text-less">{title}</span>
            {children}
        </div>
    );
};

type OrderItemRowInfoProps = {
    title: string;
    children: ReactNode;
};

export const OrderItemRowInfo: FC<OrderItemRowInfoProps> = ({ title, children }) => {
    return (
        <div className="vl:flex-row vl:gap-3 vl:items-center flex flex-col gap-1 text-sm">
            <span className="text-text-less font-secondary min-w-[100px] font-semibold">{title}</span>
            {children}
        </div>
    );
};
