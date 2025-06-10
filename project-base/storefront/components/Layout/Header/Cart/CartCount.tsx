export const CartCount: FC = ({ children }) => (
    <span className="bg-background-accent font-secondary text-text-inverted vl:-right-3 vl:-top-[6.5px] absolute top-1 right-1 flex h-5 min-w-5 items-center justify-center rounded-full px-0.5 text-xs leading-normal font-bold">
        {children}
    </span>
);
