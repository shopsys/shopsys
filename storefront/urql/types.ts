export type TokenType = {
    accessToken: string;
    refreshToken: string;
};

export type OptionalTokenType = Partial<TokenType>;
