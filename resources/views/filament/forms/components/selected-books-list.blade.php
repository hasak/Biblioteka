<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $books = $getBooks();
    @endphp

    <style>
        .selected-books-count {
            font-size: .8125rem;
            line-height: 1.25rem;
            margin-bottom: .5rem;
        }

        .selected-books {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            /* long selections scroll rather than stretching the modal */
            max-height: 24rem;
            overflow-y: auto;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .selected-books-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .25rem .5rem;
            border-radius: .375rem;
            background: var(--gray-50);
        }

        .dark .selected-books-row { background: var(--gray-800) }

        .selected-books-title {
            flex: 1;
            min-width: 0;
            font-size: .8125rem;
            line-height: 1.25rem;
        }

        .selected-books-author,
        .selected-books-shelf {
            font-size: .75rem;
            opacity: .6;
        }

        .selected-books-author { display: block }
        .selected-books-shelf { font-variant-numeric: tabular-nums }
    </style>

    <div
        x-data="{
            kept: [],
            statePath: @js($getStatePath()),
            init() {
                this.kept = this.$wire.get(this.statePath) ?? [];
                this.$watch(() => this.$wire.get(this.statePath), value => this.kept = value ?? []);
            },
            keeps(id) {
                return this.kept.some(keptId => keptId == id);
            },
            drop(id) {
                this.kept = this.kept.filter(keptId => keptId != id);
                this.$wire.set(this.statePath, this.kept);
            },
        }"
        {{ $getExtraAttributeBag() }}
    >
        <p class="selected-books-count">
            Books for move: <span x-text="kept.length"></span>
        </p>

        <p class="selected-books-count" x-show="! kept.length" x-cloak>
            Nothing left to move. Drop the modal or put a book back by starting again.
        </p>

        <ul class="selected-books">
            @foreach ($books as $book)
                <li class="selected-books-row" x-show="keeps({{ $book['id'] }})" x-cloak>
                    <span class="selected-books-title">
                        {{ $book['title'] }}
                        <span class="selected-books-author">{{ $book['author'] }}</span>
                    </span>

                    <span class="selected-books-shelf">{{ $book['position'] }}</span>

                    <x-filament::icon-button
                        :icon="\Filament\Support\Icons\Heroicon::XMark"
                        color="danger"
                        size="sm"
                        label="Leave this book where it is"
                        x-on:click="drop({{ $book['id'] }})"
                    />
                </li>
            @endforeach
        </ul>
    </div>
</x-dynamic-component>
