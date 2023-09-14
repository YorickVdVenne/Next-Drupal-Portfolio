import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'
import Navigation from '@components/organisms/Navigation/Component'
import Footer from '@components/organisms/Footer/Component'

export default function App ({ Component, pageProps }: AppProps): JSX.Element {
  const apolloClient = useApollo(pageProps.initialApolloState)

  return (
    <ApolloProvider client={apolloClient}>
      <Navigation />
      <div className='stack'>
        <div className='content'>
          <Component {...pageProps} />
        </div>
        <Footer />
      </div>
    </ApolloProvider>
  )
}
