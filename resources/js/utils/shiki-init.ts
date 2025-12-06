// Dynamic import to handle Vite module resolution
let shikiModule: any = null;
let shikiLoadPromise: Promise<any> | null = null;

async function loadShiki() {
    if (!shikiLoadPromise) {
        shikiLoadPromise = import('shiki').then(module => {
            shikiModule = module;
            return module;
        }).catch(error => {
            console.error('Failed to load shiki:', error);
            shikiLoadPromise = null; // Reset on error so we can retry
            throw error;
        });
    }
    return shikiLoadPromise;
}

// Cache for loaded languages
const loadedLanguages = new Set<string>();

export async function getShikiHighlighter() {
    const shiki = await loadShiki();
    return {
        codeToHtml: shiki.codeToHtml,
        getLoadedLanguages: () => Array.from(loadedLanguages),
    };
}

export async function highlightCode(code: string, language: string): Promise<string> {
    try {
        const shiki = await loadShiki();
        
        if (!shiki || !shiki.codeToHtml) {
            throw new Error('codeToHtml not available in shiki module');
        }
        
        const lang = (language || 'text').toLowerCase();
        
        // Map common aliases
        const langMap: Record<string, string> = {
            'js': 'javascript',
            'ts': 'typescript',
            'py': 'python',
            'rb': 'ruby',
            'sh': 'bash',
            'yml': 'yaml',
            'md': 'markdown',
        };
        
        const mappedLang = langMap[lang] || lang;
        
        // Track loaded languages
        if (!loadedLanguages.has(mappedLang)) {
            loadedLanguages.add(mappedLang);
        }
        
        const html = await shiki.codeToHtml(code, {
            lang: mappedLang,
            theme: 'github-dark',
        });
        
        return html;
    } catch (error: any) {
        console.warn('Shiki highlighting error:', error);
        console.warn('Error details:', {
            message: error?.message,
            stack: error?.stack,
            language: language,
        });
        // Fallback: escape HTML and return plain text
        return `<pre class="m-0 p-0"><code>${escapeHtml(code)}</code></pre>`;
    }
}

function escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

