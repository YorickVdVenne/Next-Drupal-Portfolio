import React from 'react'
import { Button } from './Component'

export default {
  title: 'Atoms/Buttons'
}

export const Default = () => {
  return (
    <div style={{ marginBlock: 'var(--spacing-4x)' }}>
      <Button>Button</Button>
    </div>
  )
}
