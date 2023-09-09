import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'

export default function App ({ Component, pageProps }: AppProps): JSX.Element {
  const apolloClient = useApollo(pageProps.initialApolloState)

  return (
    <ApolloProvider client={apolloClient}>
      <Component {...pageProps} />
    </ApolloProvider>
  )
}
