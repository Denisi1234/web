<?php
/**
 * fastnetstays.com - Navbar Element Alias
 * Forwards directly to the unified shared-header element.
 */
echo $this->element('shared-header', $this->viewVars);
// Real working portal: auto-render portal sub-nav below site header for admin/owner sections
try {
    $navPath = $this->getRequest()->getPath() ?: '/';
    if (str_starts_with($navPath, '/admin') || str_starts_with($navPath, '/host') || str_starts_with($navPath, '/owner')) {
        echo $this->element('portal_nav', $this->viewVars);
    }
} catch (\Throwable $e) {
    // never break header on nav errors
}