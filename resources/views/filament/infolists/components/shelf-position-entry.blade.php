<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <style>
        .shelf-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .cell {
            border: 1px solid gray;
            cursor: default;
            padding: 0;
        }
        .cell::after {
            content: '';
            display: block;
            padding-top: 100%;
        }
        .cell-large::after { padding-top: 50%; }
        .cell-selected { background-color: {{ \Filament\Support\Colors\Color::Blue[500] }}; }
        .shelf-table col.c-small { width: calc(100% / 9); }
        .shelf-table col.c-large { width: calc(200% / 9); }
    </style>
    <div {{ $getExtraAttributeBag() }}>
        <table class="shelf-table">
            <colgroup>
                <col class="c-small">
                <col class="c-large">
                <col class="c-small">
                <col class="c-large">
                <col class="c-large">
                <col class="c-small">
            </colgroup>
            @for($i=1;$i<=6;$i++)
                <tr>
                    @for($j=1;$j<=6;$j++)
                        <td class="cell {{ $j==2||$j==4||$j==5 ? 'cell-large' : 'cell-small' }} {{ $i==$record->shelf_y&&$j==$record->shelf_x ? 'cell-selected' : '' }}"></td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
</x-dynamic-component>
