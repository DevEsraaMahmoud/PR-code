/**
 * PrismJS configuration and language registration
 * Registers common languages used in PR Code posts
 */

// Import the properly initialized Prism instance
import Prism from '@/utils/prism-init';

/**
 * Highlight code element using Prism
 * @param element - The code element to highlight
 */
export function highlightCode(element: HTMLElement | null): void {
  if (!element) return;
  
  try {
    // Ensure Prism is loaded
    if (typeof Prism === 'undefined' || !Prism.highlightElement) {
      console.warn('PrismJS not properly loaded');
      return;
    }
    
    // Ensure the language is registered
    const languageMatch = element.className.match(/language-(\w+)/);
    const language = languageMatch?.[1];
    
    if (language) {
      const normalizedLang = normalizeLanguage(language);
      if (normalizedLang !== language) {
        element.className = element.className.replace(/language-\w+/, `language-${normalizedLang}`);
      }
      
      // Check if language is registered
      if (!Prism.languages[normalizedLang]) {
        console.warn(`Prism language "${normalizedLang}" not loaded. Falling back to text.`);
        element.className = element.className.replace(/language-\w+/, 'language-text');
      }
    }
    
    Prism.highlightElement(element);
  } catch (error) {
    console.error('Error highlighting code:', error);
    // Fallback: just show the code without highlighting
  }
}

/**
 * Highlight all code elements within a container
 * @param container - Container element containing code blocks
 */
export function highlightAll(container: HTMLElement | null): void {
  if (!container) return;
  const codeElements = container.querySelectorAll('code[class*="language-"]');
  codeElements.forEach((el) => {
    if (el instanceof HTMLElement) {
      Prism.highlightElement(el);
    }
  });
}

/**
 * Get supported language list
 */
export const SUPPORTED_LANGUAGES = [
  'php',
  'javascript',
  'typescript',
  'python',
  'java',
  'bash',
  'sql',
  'json',
  'markdown',
  'css',
  'scss',
] as const;

export type SupportedLanguage = typeof SUPPORTED_LANGUAGES[number];

/**
 * Normalize language identifier to Prism-compatible format
 */
export function normalizeLanguage(lang: string): string {
  const normalized = lang.toLowerCase().trim();
  return SUPPORTED_LANGUAGES.includes(normalized as SupportedLanguage)
    ? normalized
    : 'text';
}

