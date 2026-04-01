<script lang="ts">
    let { name, active = false } = $props<{ name: IconName; active?: boolean }>();

    export type IconName =
        | 'dashboard' | 'shop' | 'orders' | 'questions' | 'items'
        | 'admins' | 'users' | 'reports' | 'profile' | 'logout';
</script>

<span class={`flex h-6 w-6 shrink-0 items-center justify-center transition-transform duration-300 ease-out ${active ? 'scale-105' : 'scale-100'}`}>
    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width={active ? "2" : "1.5"} viewBox="0 0 24 24">
        {#if name === 'dashboard'}
            <path d="M4 5h7v6H4z" />
            <path d="M13 5h7v10h-7z" />
            <path d="M4 13h7v6H4z" />
            <path d="M13 17h7v2h-7z" />
        {:else if name === 'shop'}
            <path d="M5 8h14l-1 11H6L5 8Z" />
            <path d="M9 8V6a3 3 0 0 1 6 0v2" />
        {:else if name === 'orders'}
            <path d="M7 4h10" />
            <path d="M6 8h12" />
            <path d="M8 12h8" />
            <path d="M9 16h6" />
            <path d="M5 4h14v16H5z" />
        {:else if name === 'questions'}
            <path d="M7 9h10" />
            <path d="M7 13h6" />
            <path d="M7 17h3" />
            <path d="M5 5h14v11H9l-4 3V5z" />
        {:else if name === 'items'}
            <path d="M12 3l8 4.5-8 4.5-8-4.5L12 3z" />
            <path d="M4 7.5V16.5L12 21l8-4.5V7.5" />
            <path d="M12 12v9" />
        {:else if name === 'admins'}
            <path d="M12 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
            <path d="M4 20a8 8 0 0 1 16 0" />
            <path d="M18 7l1.5 1.5L22 6" />
        {:else if name === 'users'}
            <path d="M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path d="M17 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path d="M3.5 19a5.5 5.5 0 0 1 11 0" />
            <path d="M14.5 19a5.5 5.5 0 0 1 6 0" />
        {:else if name === 'reports'}
            <path d="M6 4v16" />
            <path d="M6 5h10l-2 4 2 4H6" />
            <path d="M10 18h8" />
        {:else if name === 'profile'}
            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
            <path d="M5 20a7 7 0 0 1 14 0" />
            <path d="M19 7v4" />
            <path d="M17 9h4" />
        {:else if name === 'logout'}
            <path d="M10 17l5-5-5-5" />
            <path d="M15 12H3" />
            <path d="M21 21V3" />
        {/if}
    </svg>
</span>