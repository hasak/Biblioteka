<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        // Derive the sibling paths from this field's own state path, so the grid
        // also works inside modals, where the prefix is not "data.".
        $statePrefix = \Illuminate\Support\Str::beforeLast($getStatePath(), '.');
        $xStatePath = $statePrefix . '.shelf_x';
        $yStatePath = $statePrefix . '.shelf_y';
    @endphp

    <div
        x-data="{
            state: { x: null, y: null },
            xPath: @js($xStatePath),
            yPath: @js($yStatePath),
            init() {
                this.state.x = this.$wire.get(this.xPath);
                this.state.y = this.$wire.get(this.yPath);
                this.$watch(() => this.$wire.get(this.xPath), value => this.state.x = value);
                this.$watch(() => this.$wire.get(this.yPath), value => this.state.y = value);
            },
            select(x, y) {
                this.$wire.set(this.xPath, x);
                this.$wire.set(this.yPath, y);
                this.state = { x: x, y: y };
            },
        }"
        {{ $getExtraAttributeBag() }}
    >
        <x-shelf-grid :interactive="true" />
    </div>
</x-dynamic-component>
