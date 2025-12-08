@if($paginator->total() > 0)
    <div class="{{ $wrapperClass }}">
        @if($showInfo)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">
                    Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
                </div>
            </div>
        @endif
        @if($paginator->hasPages())
            <div class="d-flex justify-content-center">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
@endif
