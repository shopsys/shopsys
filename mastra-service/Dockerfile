FROM node:20-alpine
WORKDIR /home/node/app

# Install deps first (better layer caching)
COPY package.json pnpm-lock.yaml* ./
RUN corepack enable && pnpm i --frozen-lockfile

# Copy source
COPY . .

# Dev server port
EXPOSE 4111

# Start Mastra dev (bind port 4111 via your Mastra server config)
CMD ["pnpm", "run", "dev"]
