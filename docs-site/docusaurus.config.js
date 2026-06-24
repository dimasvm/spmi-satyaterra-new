const { themes } = require('prism-react-renderer');

/** @type {import('@docusaurus/types').Config} */
const config = {
    title: 'Panduan Aplikasi SPMI',
    tagline: 'Panduan penggunaan aplikasi SPMI untuk admin LPM, unit, auditor, dan pimpinan.',
    favicon: 'img/favicon.ico',
    url: process.env.DOCS_URL ?? 'http://localhost:8000',
    baseUrl: process.env.DOCS_BASE_URL ?? '/docs/',
    organizationName: 'spmi',
    projectName: 'spmi-satyaterra-new',
    onBrokenLinks: 'throw',
    markdown: {
        hooks: {
            onBrokenMarkdownLinks: 'warn',
        },
    },
    trailingSlash: true,
    presets: [
        [
            'classic',
            {
                docs: {
                    routeBasePath: '/',
                    sidebarPath: require.resolve('./sidebars.js'),
                },
                blog: false,
                theme: {
                    customCss: require.resolve('./src/css/custom.css'),
                },
            },
        ],
    ],
    themeConfig: {
        image: 'img/logo-kampus.png',
        navbar: {
            title: 'SPMI',
            logo: {
                alt: 'Logo kampus',
                src: 'img/logo-kampus.png',
            },
            items: [
                {
                    type: 'docSidebar',
                    sidebarId: 'tutorialSidebar',
                    position: 'left',
                    label: 'Panduan',
                },
                {
                    to: '/',
                    label: 'Beranda',
                    position: 'right',
                },
            ],
        },
        footer: {
            style: 'light',
            links: [],
            copyright: `Copyright © ${new Date().getFullYear()} SPMI Satyaterra`,
        },
        prism: {
            theme: themes.github,
            darkTheme: themes.dracula,
        },
    },
};

module.exports = config;
