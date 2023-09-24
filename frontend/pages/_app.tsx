import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'
import Navigation from '@components/organisms/Navigation/Component'
import Footer from '@components/organisms/Footer/Component'
import SideElement, { DisplayOption, Orientation } from '@components/molecules/SideElement/Component'

export default function App ({ Component, pageProps }: AppProps): JSX.Element {
  const apolloClient = useApollo(pageProps.initialApolloState)

  return (
    <ApolloProvider client={apolloClient}>
      <Navigation />
      <SideElement orientation={Orientation.left} displayOption={DisplayOption.socials}/>
      <SideElement orientation={Orientation.right} displayOption={DisplayOption.mail}/>
      <div className='stack'>
        <div className='content'>
          <Component {...pageProps} />
        </div>
        <Footer />
      </div>
    </ApolloProvider>
  )
}
