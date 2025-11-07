import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import App from '../App'

// Mock the hooks to control their behavior in tests
vi.mock('../hooks', () => ({
  useAuth: vi.fn(),
  useSync: vi.fn(),
  useMessage: vi.fn()
}))

import { useAuth, useSync, useMessage } from '../hooks'

describe('App Component', () => {
  const mockCheckStatus = vi.fn()
  const mockSyncAccounts = vi.fn()
  const mockSyncVendors = vi.fn()
  const mockShowMessage = vi.fn()

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

    // Default mock implementations
    useAuth.mockReturnValue({
      configured: true,
      connected: true,
      checkStatus: mockCheckStatus.mockResolvedValue({})
    })

    useSync.mockReturnValue({
      loading: false,
      accounts: [
        { AccountID: '1', Name: 'Cash Account', Type: 'BANK' }
      ],
      vendors: [
        { ContactID: '1', Name: 'Test Vendor', IsSupplier: true }
      ],
      syncAccounts: mockSyncAccounts.mockResolvedValue({ count: 1 }),
      syncVendors: mockSyncVendors.mockResolvedValue({ count: 1 })
    })

    useMessage.mockReturnValue({
      msg: '',
      showMessage: mockShowMessage
    })
  })

  it('should render main elements when connected', () => {
    render(<App />)

    expect(screen.getByText('Coding Assessment')).toBeInTheDocument()
    expect(screen.getByText('Test Connection')).toBeInTheDocument()
    expect(screen.getByText('Sync Accounts')).toBeInTheDocument()
    expect(screen.getByText('Sync Vendors')).toBeInTheDocument()
    expect(screen.getByText('Connected')).toBeInTheDocument()
  })

  it('should show not configured state', () => {
    useAuth.mockReturnValue({
      configured: false,
      connected: false,
      checkStatus: mockCheckStatus
    })

    useMessage.mockReturnValue({
      msg: 'Please configure XERO_CLIENT_ID and XERO_CLIENT_SECRET',
      showMessage: mockShowMessage
    })

    render(<App />)

    expect(screen.getByText('Not configured')).toBeInTheDocument()
    expect(screen.getByText('Configuration Required:')).toBeInTheDocument()
  })

  it('should handle sync accounts button click', async () => {
    render(<App />)

    const syncButton = screen.getByText('Sync Accounts')
    fireEvent.click(syncButton)

    await waitFor(() => {
      expect(mockSyncAccounts).toHaveBeenCalled()
      expect(mockShowMessage).toHaveBeenCalledWith('Syncing…', false)
    })
  })

  it('should disable sync buttons when not connected', () => {
    useAuth.mockReturnValue({
      configured: true,
      connected: false,  // Not connected
      checkStatus: mockCheckStatus
    })

    render(<App />)

    const syncAccountsButton = screen.getByText('Sync Accounts')
    const syncVendorsButton = screen.getByText('Sync Vendors')

    expect(syncAccountsButton).toBeDisabled()
    expect(syncVendorsButton).toBeDisabled()
  })
})
