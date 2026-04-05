<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <style>
        .shelf-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .shelf-table tr {
            /* row height = 1 unit. Use a % padding trick since aspect-ratio on tr is unreliable */
        }
        .cell {
            border: 1px solid gray;
            cursor: pointer;
            padding: 0;
            position: relative;
        }
        /* aspect-ratio shim: inner div forces the height */
        .cell::after {
            content: '';
            display: block;
            padding-top: 100%; /* 1:1 for small cells (1 unit wide) */
        }
        .cell-selected {
            background-color: {{ \Filament\Support\Colors\Color::Blue[500] }};
        }
        /* column widths as % of 9 units */
        .shelf-table col.c-small { width: calc(100% / 9); }
        .shelf-table col.c-large { width: calc(200% / 9); }
        /* large cells need 2:1 ratio (half the height of their width) */
        .cell-large::after { padding-top: 50%; }
    </style>
    <div
        x-data="{
            state: { x: null, y: null },
            init() {
                this.state.x = this.$wire.get('data.shelf_x');
                this.state.y = this.$wire.get('data.shelf_y');
                this.$watch(() => this.$wire.get('data.shelf_x'), value => this.state.x = value);
                this.$watch(() => this.$wire.get('data.shelf_y'), value => this.state.y = value);
            }
        }"
        {{ $getExtraAttributeBag() }}
    >
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
                        <td
                            class="cell {{ $j==2||$j==4||$j==5 ? 'cell-large' : 'cell-small' }}"
                            @click="$wire.set('data.shelf_x',{{$j}});$wire.set('data.shelf_y',{{$i}});state={x:{{$j}},y:{{$i}}};"
                            :class="state?.x=={{$j}}&&state?.y=={{$i}}?'cell-selected':''"
                        ></td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
    <script>

    </script>
</x-dynamic-component>
