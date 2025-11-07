import { vi } from 'vitest'
import '@testing-library/jest-dom'

// Mock fetch for tests
global.fetch = vi.fn()

// Reset mocks before each test
beforeEach(() => {
  fetch.mockClear()
})
