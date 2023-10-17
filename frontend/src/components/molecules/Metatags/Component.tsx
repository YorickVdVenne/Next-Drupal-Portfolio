import Head from 'next/head'
import React from 'react'
import { defaultTags } from '@constants/default-metatags'
import { hasValue } from '@misc/helpers'

export default function Metatags (props: MetatagsFragment): JSX.Element {
  return (
    <Head>
      <title key='title'>{props.title ?? defaultTags.title}</title>

      <meta
        name='description'
        content={props.description ?? defaultTags.description}
      />

      <meta
        property='og:title'
        key='og:title'
        content={props.og.title ?? defaultTags.title}
      />

      <meta
        property='og:description'
        key='og:description'
        content={props.og.description ?? defaultTags.description}
      />

      {hasValue(props.og.image)
        ? (
          <meta
            property='og:image'
            key='og:image'
            content={`${props.og.image ?? ''}?tr=w-1200,h-627`}
          />
          )
        : (
          <meta
            property='og:image'
            key='og:image'
            content={`${defaultTags.og_image}`}
          />
          )}

      <meta property='og:image:width' key='og:image:width' content='1200' />

      <meta property='og:image:height' key='og:image:height' content='627' />

      <meta
        property='og:url'
        key='og:url'
        content={props.canonicalUrl}
      />

      <meta property='og:type' key='og:type' content='website' />

      <meta
        property='og:site_name'
        key='og:site_name'
        content='Aviko Food Service'
      />

      <meta
        property='og:locale'
        key='og:locale'
        content={localeIdentifier.locale}
      />

      <meta
        property='fb:app_id'
        key='fb:app_id'
        content={defaultTags.fb_app_id}
      />

      <meta name='twitter:card' key='twitter:card' content='summary' />

      <meta name='twitter:title' content={props.og.title ?? defaultTags.title} />
      <meta
        name='twitter:description'
        content={props.og.description ?? defaultTags.description}
      />

      <meta
        name='twitter:image'
        content={`${props.og.image ?? defaultTags.og_image}`}
      />

      <meta name='twitter:site' content={defaultTags.twitterHandler} />

      <meta
        name='robots'
        key='robots'
        content={props.robots ?? defaultTags.robots}
      />

    </Head>
  )
}
