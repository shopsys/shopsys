export const CartCount: FC = ({ children }) => (
    <span className="bg-backgroundAccent font-secondary text-text-inverted vl:-right-2 vl:-top-[6.5px] absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full px-0.5 text-[10px] leading-normal font-bold">
        {children}
    </span>
);
