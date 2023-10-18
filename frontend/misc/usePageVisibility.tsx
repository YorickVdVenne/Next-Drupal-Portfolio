import { useTranslation } from 'next-i18next'
import { useEffect } from 'react'

export function usePageVisibility (): void {
  const { t } = useTranslation('common')

  useEffect(() => {
    const handleVisibilityChange = (): void => {
      if (document.hidden) {
        document.title = t('hiddenTitle') // Change the title when inactive
      } else {
        document.title = t('documentTitle')
      }
    }

    document.addEventListener('visibilitychange', handleVisibilityChange)

    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange)
    }
  }, [])
}
