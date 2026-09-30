<style>
    :root {
        --helpdesk-blue: #2563eb;
        --helpdesk-indigo: #4f46e5;
    }

    body.fi-body.fi-panel-admin {
        background-color: #f3f4f6;
        background-image:
            radial-gradient(ellipse at 0% 0%, rgba(96, 165, 250, .16), transparent 35%),
            radial-gradient(ellipse at 100% 100%, rgba(129, 140, 248, .14), transparent 35%);
        background-attachment: fixed;
    }

    .dark body.fi-body.fi-panel-admin,
    body.fi-body.fi-panel-admin.dark {
        background-color: #111827;
        background-image:
            radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, .12), transparent 35%),
            radial-gradient(ellipse at 100% 100%, rgba(79, 70, 229, .12), transparent 35%);
    }

    .fi-main-ctn,
    .fi-main,
    .fi-topbar,
    .fi-sidebar,
    .fi-sidebar-header,
    .fi-wi-widget,
    .fi-fo-component-ctn {
        background: transparent !important;
        box-shadow: none !important;
    }

    .fi-ta-ctn {
        background: rgba(255, 255, 255, .96) !important;
        box-shadow: none !important;
    }

    .fi-ta-table {
        background: transparent !important;
    }

    .fi-ta-row {
        background: rgba(255, 255, 255, .96);
    }

    .fi-ta-row:hover {
        background: rgba(239, 246, 255, .9) !important;
    }

    .dark .fi-ta-ctn,
    .dark .fi-ta-row {
        background: rgba(31, 41, 55, .96) !important;
    }

    .dark .fi-ta-row:hover {
        background: rgba(55, 65, 81, .8) !important;
    }

    .fi-topbar {
        background: rgba(255, 255, 255, .82) !important;
        border-color: rgba(226, 232, 240, .8) !important;
        backdrop-filter: blur(14px);
    }

    .dark .fi-topbar {
        background: rgba(17, 24, 39, .82) !important;
        border-color: rgba(55, 65, 81, .75) !important;
    }

    .fi-sidebar,
    .fi-sidebar-header {
        background: rgba(255, 255, 255, .9) !important;
        border-color: rgba(226, 232, 240, .8) !important;
    }

    .dark .fi-sidebar,
    .dark .fi-sidebar-header {
        background: rgba(17, 24, 39, .94) !important;
        border-color: rgba(55, 65, 81, .75) !important;
    }

    .fi-section {
        border-radius: 1rem !important;
        border-color: rgba(226, 232, 240, .9) !important;
        box-shadow: 0 8px 24px -22px rgba(15, 23, 42, .24) !important;
    }

    .fi-ta-ctn,
    .fi-fo-component-ctn {
        border-radius: 1rem !important;
        border-color: rgba(226, 232, 240, .9) !important;
    }

    .fi-modal-window,
    .fi-dropdown-panel {
        border-radius: 1rem !important;
        box-shadow: 0 16px 40px -24px rgba(15, 23, 42, .3) !important;
    }

    .dark .fi-section,
    .dark .fi-ta-ctn,
    .dark .fi-fo-component-ctn {
        border-color: rgba(55, 65, 81, .85) !important;
    }

    .dark .fi-section {
        box-shadow: 0 8px 24px -22px rgba(0, 0, 0, .55) !important;
    }

    .dark .fi-modal-window,
    .dark .fi-dropdown-panel {
        box-shadow: 0 16px 40px -24px rgba(0, 0, 0, .75) !important;
    }

    .fi-ta-header-cell,
    .fi-ta-header {
        background: rgba(248, 250, 252, .85);
    }

    .dark .fi-ta-header-cell,
    .dark .fi-ta-header {
        background: rgba(17, 24, 39, .6);
    }

    .fi-btn-color-primary {
        box-shadow: none !important;
    }

    body.fi-page-filament-auth-custom-login,
    body.fi-page-filament-auth-custom-request-password-reset,
    body.fi-page-filament-auth-custom-reset-password {
        background-color: #f3f4f6;
        background-image:
            radial-gradient(ellipse at 10% 10%, rgba(96, 165, 250, .3), transparent 40%),
            radial-gradient(ellipse at 90% 90%, rgba(129, 140, 248, .28), transparent 40%);
    }

    .dark body.fi-page-filament-auth-custom-login,
    .dark body.fi-page-filament-auth-custom-request-password-reset,
    .dark body.fi-page-filament-auth-custom-reset-password {
        background-color: #111827;
        background-image:
            radial-gradient(ellipse at 10% 10%, rgba(37, 99, 235, .18), transparent 40%),
            radial-gradient(ellipse at 90% 90%, rgba(79, 70, 229, .18), transparent 40%);
    }

    .fi-simple-layout {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem !important;
    }

    .fi-simple-main {
        width: min(100%, 30rem);
        border: 1px solid rgba(226, 232, 240, .9) !important;
        border-radius: 1.25rem !important;
        background: rgba(255, 255, 255, .92) !important;
        box-shadow: 0 24px 64px -32px rgba(15, 23, 42, .35) !important;
        backdrop-filter: blur(16px);
    }

    .dark .fi-simple-main {
        border-color: rgba(55, 65, 81, .85) !important;
        background: rgba(31, 41, 55, .94) !important;
    }

    .fi-input-wrp {
        border-radius: .75rem !important;
    }

    .fi-simple-main .fi-btn {
        border-radius: .75rem;
    }

    @media (max-width: 640px) {
        .fi-main { padding-inline: 1rem !important; }
        .fi-section, .fi-ta-ctn { border-radius: .875rem !important; }
    }
</style>
