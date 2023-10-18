import '../theme/main.css'
import type { AppProps } from 'next/app'
import { ApolloProvider } from '@apollo/client'
import { useApollo } from '../src/lib/apolloClient'
import { appWithTranslation } from 'next-i18next'
import Navigation from '@components/organisms/Navigation/Component'
import Footer from '@components/organisms/Footer/Component'
import SideElement, { Orientation } from '@components/molecules/SideElement/Component'
import Favicons from '@components/molecules/Favicons/Component'
import { usePageVisibility } from '../misc/usePageVisibility'
import { Menus } from '@graphql/menus'
import DefaultMetatags from '@components/molecules/DefaultMetatags/Component'

export interface GlobalPageProps {
  menus: Menus
  initialApolloState: any
}

interface Props extends AppProps {
  pageProps: GlobalPageProps & {
    children?: React.ReactNode
  }
}

function App (props: Props): JSX.Element {
  const apolloClient = useApollo(props.pageProps.initialApolloState)
  usePageVisibility()

  return (
    <>
      <DefaultMetatags />
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

export default appWithTranslation(App);