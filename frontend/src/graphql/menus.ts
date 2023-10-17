export interface MenuItem {
    label: string;
    url: string;
}

export interface ActionButton {
    label: string;
    url: string;
    icon?: string;
}

export interface SocialItem {
    label: string;
    icon: string;
    url: string;
}

export interface EmailItem {
    email: string;
    url: string;
}

export interface MainMenu {
    links: MenuItem[]
    actionButton: ActionButton;
}

export interface FooterData {
    socials: SocialItem[];
    actionButton: ActionButton;
}

export interface SideElement {
    socials: SocialItem[];
    email: EmailItem;
}

export interface Menus {
    mainMenu: MainMenu
    footer: FooterData
    sideElement: SideElement
}