import React from "react";
import Link from "next/link";
import { hasValue } from "@misc/helpers";

export function hasExtension(href: string): boolean {
  // Regex from https://stackoverflow.com/questions/6238351/fastest-way-to-detect-external-urls
  return /[-a-zA-Z0-9@:%_+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_+.~#?&//=]*)?/gi.test(
    href
  );
}

interface LinkProps {
  locale?: string;
}

export const InternalOrExternalLink = ({
  href,
  target,
  children,
  ...props
}: LinkProps & React.AnchorHTMLAttributes<HTMLAnchorElement>): JSX.Element => {
  if (!hasValue(href)) {
    href = "";
  }
  const isExternal = hasExtension(href);

  if (isExternal) {
    // removing noreferrer as a 'hack' because target self and noreferrer didnt seem to work together
    const noreferrer = target === "_self" ? "" : "noreferrer";
    return (
      <a
        href={href}
        target={target ?? "_blank"}
        rel={`${noreferrer} noopener`}
        {...props}
      >
        {children}
      </a>
    );
  }

  if (
    href.startsWith("#") ??
    href.startsWith("tel:") ??
    href.startsWith("mailto:")
  ) {
    return (
      <a href={href} target={target ?? "_self"} {...props}>
        {children}
      </a>
    );
  }

  return href !== "" ? (
    <Link href={href} target={target ?? "_self"} rel="noopener" {...props}>
      <>{children}</>
    </Link>
  ) : (
    <p className={props.className}>{children}</p>
  );
};

export default InternalOrExternalLink;
