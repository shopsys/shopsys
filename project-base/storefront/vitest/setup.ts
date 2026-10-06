import { defaultTestConfig } from './helpers/mockPublicConfig';
import '@testing-library/jest-dom/vitest';

window.__ENV = { ...defaultTestConfig };
