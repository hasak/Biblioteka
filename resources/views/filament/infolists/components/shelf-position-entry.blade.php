<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div {{ $getExtraAttributeBag() }}>
        <table style="border: 1px solid gray;">
            @for($i=1;$i<=6;$i++)
                <tr>
                    @for($j=1;$j<=6;$j++)
                        <td style="width: {{$j==2||$j==4||$j==5?40:20}}px; height: 20px; border: 1px solid gray; background-color: {{$i==$record->shelf_y&&$j==$record->shelf_x?\Filament\Support\Colors\Color::Blue[500]:"none"}};"></td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
</x-dynamic-component>
