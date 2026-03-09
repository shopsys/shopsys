import { Mastra } from '@mastra/core';
import { registerCopilotKit } from '@ag-ui/mastra/copilotkit';
import { PinoLogger } from '@mastra/loggers';
import { LibSQLStore } from '@mastra/libsql';
import {
  CloudExporter,
  DefaultExporter,
  Observability,
  SensitiveDataFilter,
} from '@mastra/observability';
import { weatherWorkflow } from './workflows/weather-workflow';
import { weatherAgent } from './agents/weather-agent';
import { sqlAgent } from './agents/sql-agent';
import { toolCallAppropriatenessScorer, completenessScorer, translationScorer } from './scorers/weather-scorer';

export const mastra = new Mastra({
  workflows: { weatherWorkflow },
  agents: { weatherAgent, sqlAgent },
  scorers: { toolCallAppropriatenessScorer, completenessScorer, translationScorer },

  bundler: {
    externals: true,
  },
  storage: new LibSQLStore({
    id: 'mastra-storage',
    // stores observability, scores, ... into memory storage, if it needs to persist, change to file:../mastra.db
    url: ':memory:',
  }),
  logger: new PinoLogger({
    name: 'Mastra',
    level: 'info',
  }),
  observability: new Observability({
    configs: {
      default: {
        serviceName: 'mastra-service',
        exporters: [new DefaultExporter(), new CloudExporter()],
        spanOutputProcessors: [new SensitiveDataFilter()],
      },
    },
  }),

  server: {
    apiRoutes: [
      registerCopilotKit({
        path: '/chat',
        resourceId: 'shopsys-admin',
      }),
    ],
  },
});
