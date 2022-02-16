<div class="flex items-center gap-x-2">
    <x-buttons.show :model="$model" />

    @if( $model->status == "review" )
    <x-buttons.deactivate :model="$model" />
    <x-buttons.activate :model="$model" />
    @endif
</div>
