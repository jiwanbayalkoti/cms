@if($paginator->hasPages())
    <div class="card-footer">
        <x-pagination :paginator="$paginator" />
    </div>
@endif

