import React from 'react'
import { Button } from './Component'
import { ComponentMeta } from '@storybook/react';

export default {
  title: 'Atoms/Buttons',
  component: Button,
} as ComponentMeta<typeof Button>

export const Default = (): JSX.Element => {
  return (
    <div style={{ marginBlock: 'var(--spacing-4x)' }}>
      <Button>Button</Button>
    </div>
  )
}
