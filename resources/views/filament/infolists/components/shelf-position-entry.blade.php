<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div {{ $getExtraAttributeBag() }}>
        <x-shelf-grid :x="$record->shelf_x" :y="$record->shelf_y" />
    </div>
</x-dynamic-component>
