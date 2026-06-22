import { createI18n } from 'vue-i18n';

const STORAGE_KEY = 'portfolio-locale';

const messages = {
    pt: {
        nav: {
            about: 'Sobre',
            stacks: 'Stacks',
            experience: 'Experiência',
            projects: 'Projetos',
            contact: 'Contato',
            blog: 'Blog',
        },
        blog: {
            label: 'Artigos',
            title: 'Blog',
            subtitle: 'Ideias, experimentos e aprendizados sobre software, tecnologia e desenvolvimento.',
            empty: 'Nenhum post publicado ainda.',
            readMore: 'Ler post',
            backToList: 'Todos os posts',
            related: 'Posts relacionados',
            journalIndex: 'Índice do diário',
            entries: 'publicações',
            entry: 'Publicação',
            articleFile: 'Arquivo do artigo',
            read: 'leitura',
            coverImage: 'Imagem de capa',
        },
        hero: {
            available: 'Disponível para novos projetos',
            viewProjects: 'Ver Projetos',
            contactMe: 'Entrar em contato',
            resume: 'Currículo',
        },
        stacks: {
            label: 'Tecnologias',
            title: 'Tecnologias que utilizo',
            empty: 'Nenhuma stack cadastrada ainda.',
        },
        experience: {
            label: 'Trajetória',
            title: 'Experiência',
            present: 'Atual',
            empty: 'Nenhuma experiência cadastrada ainda.',
        },
        projects: {
            label: 'Portfolio',
            title: 'Projetos em Destaque',
            demo: 'Demo',
            code: 'Código',
            empty: 'Nenhum projeto em destaque ainda.',
        },
        contact: {
            label: 'Contato',
            titlePart1: 'Vamos',
            titlePart2: 'construir algo juntos',
            titleSuffix: '?',
            subtitle: 'Aberto a oportunidades, colaborações e conversas sobre tecnologia.',
        },
        linkLibrary: {
            label: 'Minha biblioteca de links',
            ariaLabel: 'Abrir o Klink Hub, minha biblioteca de links',
        },
        footer: {
            rights: 'Todos os direitos reservados.',
            built: 'Construído com Laravel + Vue + Inertia.',
        },
        design: {
            selectedWorks: 'Trabalhos selecionados / prática digital',
            context: 'Web / Interface',
            independent: 'Desenvolvedor independente',
            profileStudy: 'Estudo de perfil',
            scroll: 'Role para explorar',
            stackNote: 'Ferramentas, linguagens e sistemas organizados como uma matriz de trabalho ativa.',
            archive: 'Índice do arquivo / seleção',
            network: 'Pontos de rede',
            activeLinks: 'links ativos',
            figure: 'Fig.',
        },
        a11y: {
            toggleTheme: { dark: 'Ativar modo claro', light: 'Ativar modo escuro' },
            toggleLocale: 'Alternar idioma',
            menu: 'Menu',
            backTop: 'Voltar ao topo',
        },
        months: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        meta: {
            defaultDescription: 'Portfolio de desenvolvimento — projetos, stacks e artigos sobre software, tecnologia e desenvolvimento.',
            blogIndexDescription: 'Artigos sobre software, tecnologia e desenvolvimento.',
        },
    },
    en: {
        nav: {
            about: 'About',
            stacks: 'Stacks',
            experience: 'Experience',
            projects: 'Projects',
            contact: 'Contact',
            blog: 'Blog',
        },
        blog: {
            label: 'Articles',
            title: 'Blog',
            subtitle: 'Ideas, experiments, and learnings about software, technology and development.',
            empty: 'No published posts yet.',
            readMore: 'Read post',
            backToList: 'All posts',
            related: 'Related posts',
            journalIndex: 'Journal index',
            entries: 'entries',
            entry: 'Entry',
            articleFile: 'Article file',
            read: 'read',
            coverImage: 'Cover image',
        },
        hero: {
            available: 'Available for new projects',
            viewProjects: 'View Projects',
            contactMe: 'Get in touch',
            resume: 'Resume',
        },
        stacks: {
            label: 'Technologies',
            title: 'Technologies I Work With',
            empty: 'No stacks registered yet.',
        },
        experience: {
            label: 'Journey',
            title: 'Experience',
            present: 'Present',
            empty: 'No experiences registered yet.',
        },
        projects: {
            label: 'Portfolio',
            title: 'Featured Projects',
            demo: 'Demo',
            code: 'Code',
            empty: 'No featured projects yet.',
        },
        contact: {
            label: 'Contact',
            titlePart1: 'Let\'s',
            titlePart2: 'build something together',
            titleSuffix: '',
            subtitle: 'Open to opportunities, collaborations, and tech conversations.',
        },
        linkLibrary: {
            label: 'My link library',
            ariaLabel: 'Open Klink Hub, my link library',
        },
        footer: {
            rights: 'All rights reserved.',
            built: 'Built with Laravel + Vue + Inertia.',
        },
        design: {
            selectedWorks: 'Selected works / digital practice',
            context: 'Web / Interface',
            independent: 'Independent developer',
            profileStudy: 'Profile study',
            scroll: 'Scroll to explore',
            stackNote: 'Tools, languages and systems arranged as an active working matrix.',
            archive: 'Archive index / selected',
            network: 'Network nodes',
            activeLinks: 'active links',
            figure: 'Fig.',
        },
        a11y: {
            toggleTheme: { dark: 'Switch to light mode', light: 'Switch to dark mode' },
            toggleLocale: 'Switch language',
            menu: 'Menu',
            backTop: 'Back to top',
        },
        months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        meta: {
            defaultDescription: 'Software development portfolio — projects, stacks and articles about software, technology and development.',
            blogIndexDescription: 'Notes on software, technology and development.',
        },
    },
};

function detectInitialLocale() {
    if (typeof window === 'undefined') return 'pt';
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'pt' || stored === 'en') return stored;
    const nav = (navigator.language || '').toLowerCase();
    return nav.startsWith('pt') ? 'pt' : 'en';
}

export const i18n = createI18n({
    legacy: false,
    locale: detectInitialLocale(),
    fallbackLocale: 'pt',
    messages,
});

export function setLocale(locale) {
    i18n.global.locale.value = locale;
    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, locale);
        document.documentElement.setAttribute('lang', locale === 'pt' ? 'pt-BR' : 'en');
    }
}

export function toggleLocale() {
    const next = i18n.global.locale.value === 'pt' ? 'en' : 'pt';
    setLocale(next);
}
