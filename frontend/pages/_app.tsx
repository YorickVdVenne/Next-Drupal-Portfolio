import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'
import Navigation from '@components/organisms/Navigation/Component'
import Footer from '@components/organisms/Footer/Component'
import SideElement, { DisplayOption, Orientation } from '@components/molecules/SideElement/Component'
import Favicons from '@components/molecules/Favicons/Component'
import Head from 'next/head'
import { usePageVisibility } from '../misc/usePageVisibility'

export default function App ({ Component, pageProps }: AppProps): JSX.Element {
  const apolloClient = useApollo(pageProps.initialApolloState)
  usePageVisibility()

  const title = "Yorick's Portfolio" 

  return (
    <>
      <Head>
        <title>{title}</title>
        <Favicons />
      </Head>
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
    </>
  )
}
