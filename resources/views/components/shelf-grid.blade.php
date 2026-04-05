@props([
    // Interactive grids bind to Alpine state; read-only ones compare in PHP.
    'interactive' => false,
    'x' => null,
    'y' => null,
])

@php
    // Columns 2, 4 and 5 are the wide bays on the real shelf; the rest are narrow.
    $largeColumns = [2, 4, 5];
    $columns = range(1, 6);
    $rows = range(1, 6);
@endphp

<style>
    .shelf-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .shelf-table .cell {
        border: 1px solid gray;
        padding: 0;
        position: relative;
    }
    /* aspect-ratio shim: the ::after padding is what gives each cell its height */
    .shelf-table .cell::after {
        content: '';
        display: block;
        padding-top: 100%; /* 1:1 for the narrow cells */
    }
    .shelf-table .cell-large::after {
        padding-top: 50%; /* wide cells are 2:1 */
    }
    .shelf-table .cell-selected {
        background-color: {{ \Filament\Support\Colors\Color::Blue[500] }};
    }
    .shelf-table col.c-small { width: calc(100% / 9); }
    .shelf-table col.c-large { width: calc(200% / 9); }
    .shelf-table.is-interactive .cell { cursor: pointer; }
</style>

<table class="shelf-table{{ $interactive ? ' is-interactive' : '' }}">
    <colgroup>
        @foreach ($columns as $column)
            <col class="{{ in_array($column, $largeColumns) ? 'c-large' : 'c-small' }}">
        @endforeach
    </colgroup>
    @foreach ($rows as $row)
        <tr>
            @foreach ($columns as $column)
                @php
                    $classes = ['cell', in_array($column, $largeColumns) ? 'cell-large' : 'cell-small'];

                    if (! $interactive && (int) $x === $column && (int) $y === $row) {
                        $classes[] = 'cell-selected';
                    }
                @endphp
                <td
                    class="{{ implode(' ', $classes) }}"
                    @if ($interactive)
                        @click="select({{ $column }}, {{ $row }})"
                        :class="state.x == {{ $column }} && state.y == {{ $row }} ? 'cell-selected' : ''"
                    @endif
                ></td>
            @endforeach
        </tr>
    @endforeach
</table>
