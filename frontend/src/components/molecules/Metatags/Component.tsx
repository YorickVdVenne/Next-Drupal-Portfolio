import Head from "next/head";
import React from "react";

import type { MetatagsFragment } from "@graphql/metatags";

export default function Metatags(props: MetatagsFragment): JSX.Element {
  return (
    <Head>
      <title key="title">{props.title}</title>

      <meta name="description" content={props.description} />

      <meta property="og:title" key="og:title" content={props.og.title} />

      <meta
        property="og:description"
        key="og:description"
        content={props.og.description}
      />

      <meta property="og:image" key="og:image" content={props.og.image} />

      <meta property="og:image:width" key="og:image:width" content="1200" />

      <meta property="og:image:height" key="og:image:height" content="627" />

      <meta property="og:type" key="og:type" content="website" />

      <meta
        property="og:site_name"
        key="og:site_name"
        content="Yoricks Portfolio"
      />
    </Head>
  );
}
