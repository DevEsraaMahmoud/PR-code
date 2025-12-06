// PrismJS initialization file
// This ensures Prism is properly initialized before language components are loaded

import Prism from 'prismjs';
import 'prismjs/themes/prism-tomorrow.css';

// CRITICAL: Initialize hooks BEFORE importing language components
// Some language components expect hooks to exist and will fail if they don't
if (typeof Prism !== 'undefined') {
    // Ensure Prism.languages exists
    if (!Prism.languages) {
        Prism.languages = {};
    }
    
    // Ensure Prism.hooks exists and is properly initialized BEFORE language imports
    if (!Prism.hooks) {
        Prism.hooks = {
            all: {},
            add: function(name: string, callback: Function) {
                if (!this.all[name]) {
                    this.all[name] = [];
                }
                this.all[name].push(callback);
            },
            run: function(name: string, env: any) {
                if (this.all[name]) {
                    this.all[name].forEach(function(callback: Function) {
                        try {
                            callback(env);
                        } catch (e) {
                            console.warn(`Prism hook error in ${name}:`, e);
                        }
                    });
                }
            }
        };
    }
    
    // Initialize common hooks that language components might expect
    // This must be done BEFORE language components are imported
    const commonHooks = [
        'before-highlight',
        'after-highlight', 
        'wrap',
        'build-placeholders',
        'before-tokenize',
        'after-tokenize',
        'complete'
    ];
    commonHooks.forEach(hook => {
        if (!Prism.hooks.all[hook]) {
            Prism.hooks.all[hook] = [];
        }
    });
    
    // Add a default build-placeholders hook implementation
    // Some language components register hooks that call buildPlaceholders
    // This ensures the hook exists and has a safe default implementation
    if (Prism.hooks.all['build-placeholders'].length === 0) {
        Prism.hooks.add('build-placeholders', function(env: any) {
            // Default implementation - does nothing but prevents errors
            // Language components can override this
            return env;
        });
    }
}

// Import languages in dependency order
// Core languages with no dependencies must come first
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-markup-templating'; // Required for PHP and other templating languages
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-clike';

// Languages that extend clike (must come after clike)
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-c';
import 'prismjs/components/prism-cpp';
import 'prismjs/components/prism-java';
import 'prismjs/components/prism-php'; // Requires markup-templating
import 'prismjs/components/prism-ruby';
import 'prismjs/components/prism-go';
import 'prismjs/components/prism-rust';

// Independent languages (no dependencies)
import 'prismjs/components/prism-typescript';
import 'prismjs/components/prism-python';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-yaml';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-shell-session';

export default Prism;

