export const CartCount: FC = ({ children }) => (
    <span className="absolute -top-1.5 vl:-top-[6.5px] -right-3 vl:-right-3 flex h-5 min-w-5 items-center justify-center rounded-full bg-background-warning px-0.5 font-bold font-secondary text-text-default text-xs leading-normal shadow-sm">
        {children}
    </span>
);
