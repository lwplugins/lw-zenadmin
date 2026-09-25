/**
 * Server-provided boot data (SettingsPage inline script `window.lwZenAdminAdmin`).
 */
const boot = window.lwZenAdminAdmin || {};

export const VERSION = boot.version || '';
export const NAMESPACE = boot.namespace || 'lw-zenadmin/v1';
export const DOCS_URL =
	boot.docsUrl || 'https://github.com/lwplugins/lw-zenadmin#readme';
