{{--
    Progress for the cover fetch, sitting under the cover upload.

    The loop lives here rather than on the server so that Cancel is instant:
    stopping simply means not asking for the next source. At worst the request
    already in flight finishes and is ignored.
--}}
<div
    wire:ignore
    x-data="coverFetch(@js(\App\Services\BookApi::coverSources()))"
    x-on:start-cover-fetch.window="start($event.detail.isbn)"
    x-show="state !== 'idle'"
    x-cloak
    class="fi-sc-component"
    style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;font-size:.8125rem;line-height:1.25rem;padding:.5rem .75rem;border-radius:.5rem;border:1px solid rgb(var(--gray-200));background:rgb(var(--gray-50))"
    x-bind:style="state === 'error' ? 'border-color:rgb(var(--danger-300))' : ''"
>
    {{-- spinner, only while working --}}
    <svg x-show="state === 'working'" style="width:1rem;height:1rem;flex-shrink:0;animation:spin 1s linear infinite" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>

    <span x-text="label" style="flex:1;min-width:12rem"></span>

    <span x-show="state === 'working'" x-text="elapsed + 's'" style="opacity:.6;font-variant-numeric:tabular-nums"></span>

    <x-filament::button
        x-show="state === 'working'"
        x-on:click="cancel()"
        size="xs"
        color="gray"
        type="button"
    >Cancel</x-filament::button>

    <x-filament::button
        x-show="state !== 'working'"
        x-on:click="start(lastIsbn)"
        x-bind:disabled="!lastIsbn"
        size="xs"
        color="gray"
        type="button"
    >Retry</x-filament::button>
</div>

@once
    @push('scripts')
        <style>@keyframes spin { to { transform: rotate(360deg) } }</style>
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
                        this.label = 'Looking for a cover…';

                        clearInterval(this.timer);
                        this.timer = setInterval(() => this.elapsed++, 1000);

                        this.$wire.resetCoverFetch();
                        this.walk(isbn, run);
                    },

                    async walk(isbn, run) {
                        let best = null;

                        for (const source of this.sources) {
                            if (run !== this.run) return;          // cancelled or restarted
                            this.label = `Looking for a cover — ${source.label}…`;

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
                        this.label = best.good
                            ? `Cover loaded from ${best.source} (${best.width}×${best.height})`
                            : `Only a small cover found — ${best.width}×${best.height} from ${best.source}. A photo will look better.`;
                    },

                    cancel() {
                        this.run++;                                 // any in-flight reply is now stale
                        clearInterval(this.timer);
                        this.state = 'done';
                        this.label = 'Cover fetch cancelled — take a photo or upload one.';
                    },
                }
            }
        </script>
    @endpush
@endonce
