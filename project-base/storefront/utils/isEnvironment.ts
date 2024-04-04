export const isEnvironment = (environment: 'development' | 'production' | 'test') =>
    process.env.NODE_ENV === environment;
