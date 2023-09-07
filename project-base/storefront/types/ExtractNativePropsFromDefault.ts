export type ExtractNativePropsFromDefault<
    T,
    TRequired extends keyof T = keyof T,
    TOptional extends keyof T = keyof T,
> = Required<Pick<T, TRequired>> & Partial<Pick<T, TOptional>>;
