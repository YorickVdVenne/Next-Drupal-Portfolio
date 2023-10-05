import React from 'react'
import dynamic from 'next/dynamic'

export const Logo = dynamic<{ className?: string }>(
  async () => await import('./logo.svg'),
  {
    loading: () => <span />,
    ssr: false
  }
)
