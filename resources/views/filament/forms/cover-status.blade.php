{{--
    Progress for the cover fetch, sitting under the cover upload.

    The loop lives here rather than on the server so that Cancel is instant:
    stopping simply means not asking for the next source. At worst the request
    already in flight finishes and is ignored.

    The panel is styled by the class below rather than inline: Alpine's
    x-bind:style replaces the whole style attribute when it is given a string,
    which quietly wiped every inline rule this element had.
--}}
<div
    wire:ignore
    x-data="coverFetch(@js(\App\Services\BookApi::coverSources()))"
    x-on:start-cover-fetch.window="start($event.detail.isbn)"
    x-show="state !== 'idle'"
    x-cloak
    class="cover-status"
    x-bind:class="{ 'cover-status-failed': state === 'error' }"
>
    {{-- spinner, only while working --}}
    <svg x-show="state === 'working'" class="cover-status-spinner" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>

    <span x-text="label" class="cover-status-label"></span>

    <span x-show="state === 'working'" x-text="elapsed + 's'" class="cover-status-elapsed"></span>

    <x-filament::button
        x-show="state === 'working'"
        x-on:click="cancel()"
        class="cover-status-action"
        size="xs"
        color="gray"
        type="button"
    >Cancel</x-filament::button>

    <x-filament::button
        x-show="state !== 'working'"
        x-on:click="start(lastIsbn)"
        x-bind:disabled="!lastIsbn"
        class="cover-status-action"
        size="xs"
        color="gray"
        type="button"
    >Retry</x-filament::button>
</div>

@once
    @push('scripts')
        <style>
            /* no frame: it is a line of status under the upload, not a panel */
            .cover-status {
                display: flex;
                align-items: center;
                gap: .5rem;
                width: 100%;
                padding-block: .25rem;
                font-size: .8125rem;
                line-height: 1.25rem;
            }

            /*
                Filament lays belowContent out as an inline flex row, so the
                wrapper it puts around this component is shrink-to-fit and the
                row stops short of the field. Stretch it so Cancel reaches the
                right edge of the upload above.
            */
            .fi-sc.fi-inline:has(> * > * > .cover-status) > * {
                flex-grow: 1;
                min-width: 0;
            }

            .cover-status-failed { color: var(--danger-600) }
            .dark .cover-status-failed { color: var(--danger-400) }

            /* sized to the text beside it, so the two share a line */
            .cover-status-spinner {
                width: .875rem;
                height: .875rem;
                flex-shrink: 0;
                animation: cover-status-spin 1s linear infinite;
            }

            /* takes the slack, and shrinks rather than pushing the button off */
            .cover-status-label { flex: 1; min-width: 0 }

            .cover-status-elapsed {
                padding-inline-end: .25rem;
                opacity: .6;
                font-variant-numeric: tabular-nums;
            }

            .cover-status-action { flex-shrink: 0; margin-inline-start: auto }

            @keyframes cover-status-spin { to { transform: rotate(360deg) } }
        </style>
        <script>
            function coverFetch(sources) {
                return {
                    sources,
                    state: 'idle',        // idle | working | done | error
                    label: '',
                    elapsed: 0,
                    lastIsbn: null,
                    run: 0,               // bumped on cancel/restart so stale replies are ignored
                    timer: null,

                    start(isbn) {
                        if (! isbn) return;
                        const run = ++this.run;

                        this.lastIsbn = isbn;
                        this.state = 'working';
                        this.elapsed = 0;
                        this.label = 'Searching for a cover';

                        clearInterval(this.timer);
                        this.timer = setInterval(() => this.elapsed++, 1000);

                        this.$wire.resetCoverFetch();
                        this.walk(isbn, run);
                    },

                    async walk(isbn, run) {
                        let best = null;

                        for (const source of this.sources) {
                            if (run !== this.run) return;          // cancelled or restarted
                            this.label = `Searching for a cover @ ${source.label}`;

                            let result;
                            try {
                                result = await this.$wire.fetchCoverStep(isbn, source.key);
                            } catch (e) {
                                continue;                           // that source failed; try the next
                            }

                            if (run !== this.run) return;           // reply arrived after cancel

                            if (result?.found && (! best || result.width > best.width)) {
                                best = result;
                            }
                            if (result?.good) break;                // big enough, stop paying for more
                        }

                        this.finish(best);
                    },

                    finish(best) {
                        clearInterval(this.timer);

                        if (! best) {
                            this.state = 'error';
                            this.label = 'No cover found — take a photo or upload one.';
                            return;
                        }

                        this.state = 'done';
                        // A request racing the fetch can empty the upload
                        // field; now that the page is quiet, have the server
                        // put the cover back.
                        this.$wire.applyFetchedCover();
                        this.label = best.good
                            ? `Cover loaded from ${best.source} (${best.width}×${best.height})`
                            : `Only a small cover found — ${best.width}×${best.height} from ${best.source}. A photo will look better.`;
                    },

                    cancel() {
                        this.run++;                                 // any in-flight reply is now stale
                        clearInterval(this.timer);
                        this.state = 'done';
                        // Whatever a source managed to store before the wait
                        // was called off is still worth keeping.
                        this.$wire.applyFetchedCover();
                        this.label = 'Cover fetch cancelled — take a photo or upload one.';
                    },
                }
            }
        </script>
    @endpush
@endonce
