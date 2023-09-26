import { useEffect } from 'react';

export function usePageVisibility() {
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.hidden) {
        document.title = 'Come Back!'; // Change the title when inactive
      } else {
        document.title = "Yorick's Portfolio" 
      }
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, []);
}