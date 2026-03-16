import { defaultTestConfig } from './helpers/mockPublicConfig';
import '@testing-library/jest-dom';

window.__ENV = { ...defaultTestConfig };
