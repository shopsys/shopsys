export const isEnvironment = (environment: 'development' | 'production' | 'test') =>
    process.env.APP_ENV === environment;
