import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'
import Navigation from '@components/organisms/Navigation/Component'
import Footer from '@components/organisms/Footer/Component'
import SideElement, { Orientation } from '@components/molecules/SideElement/Component'
import Favicons from '@components/molecules/Favicons/Component'
import Head from 'next/head'
import { usePageVisibility } from '../misc/usePageVisibility'
import { Menus } from '@graphql/menus'

export interface GlobalPageProps {
  menus: Menus
  initialApolloState: any
}

interface Props extends AppProps {
  pageProps: GlobalPageProps & {
    children?: React.ReactNode
  }
}

export default function App (props: Props): JSX.Element {
  const apolloClient = useApollo(props.pageProps.initialApolloState)
  usePageVisibility()

  const title = "Yorick's Portfolio" 

  return (
    <>
      <Head>
        <title>{title}</title>
      </Head>
      <Favicons />
      <ApolloProvider client={apolloClient}>
        <Navigation mainMenu={props.pageProps.menus.mainMenu} />
        <SideElement orientation={Orientation.left} content={props.pageProps.menus.sideElement.socials} />
        <SideElement orientation={Orientation.right} content={props.pageProps.menus.sideElement.email} />
        <div className='stack'>
          <div className='content'>
            <props.Component {...props.pageProps} />
          </div>
          <Footer footer={props.pageProps.menus.footer} />  
        </div>
      </ApolloProvider>
    </>
  )
}
