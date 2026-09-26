{{--
    Admin panel styling that does not need a compiled Filament theme, loaded
    through the `panels::styles.after` render hook in the panel provider.

    Two things happen here:

    * the sidebar is painted white, in both colour schemes,
    * the sidebar and its navigation items are sized down so the column stays
      proportional to the content area.
--}}
<style>
    .fi-sidebar {
        background-color: #ffffff !important;
    }

    .dark .fi-sidebar {
        background-color: #111827 !important; /* gray-900 */
    }

    /* Now that the sidebar is white it needs a hairline to stay distinguishable
       from the gray page background, just like the topbar has. */
    @media (min-width: 1024px) {
        .fi-sidebar {
            border-inline-end: 1px solid rgba(17, 24, 39, 0.06);
        }

        .dark .fi-sidebar {
            border-inline-end-color: rgba(255, 255, 255, 0.1);
        }
    }

    .fi-sidebar-header {
        padding-inline: 1rem;
    }

    .fi-sidebar-nav {
        padding: 1.25rem 1rem;
        row-gap: 1.25rem;
    }

    .fi-sidebar-nav-groups {
        row-gap: 1.25rem;
    }

    .fi-sidebar-group-label {
        font-size: 0.8125rem;
        line-height: 1.25rem;
    }

    .fi-sidebar-item-button {
        column-gap: 0.625rem;
        border-radius: 0.5rem;
        padding: 0.375rem 0.5rem;
    }

    .fi-sidebar-item-icon {
        height: 1.125rem;
        width: 1.125rem;
    }

    .fi-sidebar-item-label {
        font-size: 0.8125rem;
        line-height: 1.25rem;
    }
</style>
