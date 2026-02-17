@extends('admin.layout')

@section('title', 'BoQ Items – ' . $boq_work->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        @if($boq_work->parent_id)
            <p class="text-muted small mb-1">
                <a href="{{ route('admin.boq.work-index') }}">Work & BoQ</a>
                &rarr; <a href="{{ route('admin.boq.works.show', $boq_work->parent) }}">{{ $boq_work->parent->name }}</a>
                &rarr; <span class="text-dark">{{ $boq_work->name }}</span>
            </p>
        @endif
        <h1 class="h3 mb-0">BoQ Items – {{ $boq_work->name }}</h1>
        <p class="text-muted small mb-0">{{ $boq_work->type->name ?? '' }}{!! $boq_work->parent_id ? ' <span class="badge bg-secondary">Subwork</span>' : '' !!}</p>
    </div>
    <a href="{{ route('admin.boq.work-index') }}" class="btn btn-sm btn-outline-primary p-1" title="Work & BoQ"><i class="bi bi-arrow-left"></i></a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.boq.works.items.update', $boq_work) }}" method="POST" id="boqItemsForm">
    @csrf
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Items</strong>
            <button type="button" class="btn btn-sm btn-success p-1" onclick="addBoqRow()" title="Add Row"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="boqItemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30px">SN</th>
                        <th>Item Description</th>
                        <th style="width: 100px">Unit</th>
                        <th style="width: 100px">Qty</th>
                        <th style="width: 120px">Rate</th>
                        <th>Rate in Words</th>
                        <th style="width: 120px">Amount</th>
                        <th style="width: 50px"></th>
                    </tr>
                </thead>
                <tbody id="boqItemsBody">
                    @foreach($boq_work->items as $item)
                        <tr class="boq-row">
                            <td class="boq-sn">{{ $loop->iteration }}</td>
                            <td><input type="text" name="items[{{ $loop->index }}][item_description]" class="form-control form-control-sm" value="{{ old('items.'.$loop->index.'.item_description', $item->item_description) }}" placeholder="Description"></td>
                            <td><input type="text" name="items[{{ $loop->index }}][unit]" class="form-control form-control-sm" value="{{ old('items.'.$loop->index.'.unit', $item->unit) }}" placeholder="Unit"></td>
                            <td><input type="number" step="0.0001" name="items[{{ $loop->index }}][qty]" class="form-control form-control-sm boq-qty" value="{{ old('items.'.$loop->index.'.qty', $item->qty) }}" placeholder="0"></td>
                            <td><input type="number" step="0.0001" name="items[{{ $loop->index }}][rate]" class="form-control form-control-sm boq-rate" value="{{ old('items.'.$loop->index.'.rate', $item->rate) }}" placeholder="0"></td>
                            <td><input type="text" name="items[{{ $loop->index }}][rate_in_words]" class="form-control form-control-sm boq-rate-words" value="{{ old('items.'.$loop->index.'.rate_in_words', $item->rate_in_words) }}" readonly placeholder="Auto"></td>
                            <td><input type="text" class="form-control form-control-sm boq-amount" value="{{ number_format($item->amount, 2) }}" readonly></td>
                            <td><input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}"><button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeBoqRow(this)" title="Remove row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-sm btn-primary p-1" title="Save Items"><i class="bi bi-check-lg"></i></button>
        </div>
    </div>

    @if($boq_work->children->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><strong>Subworks</strong></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach($boq_work->children as $sub)
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <a href="{{ route('admin.boq.works.show', $sub) }}" class="fw-medium">{{ $sub->name }}</a>
                            <span class="text-muted">({{ $sub->items->count() }} items)</span>
                            <a href="{{ route('admin.boq.works.show', $sub) }}" class="btn btn-sm btn-outline-primary p-1" title="Edit Items"><i class="bi bi-pencil"></i></a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</form>

<script>
(function() {
    let rowIndex = {{ $boq_work->items->count() }};

    window.addBoqRow = function() {
        const tbody = document.getElementById('boqItemsBody');
        const sn = tbody.querySelectorAll('tr.boq-row').length + 1;
        const tr = document.createElement('tr');
        tr.className = 'boq-row';
        tr.innerHTML = `
            <td class="boq-sn">${sn}</td>
            <td><input type="text" name="items[${rowIndex}][item_description]" class="form-control form-control-sm" placeholder="Description"></td>
            <td><input type="text" name="items[${rowIndex}][unit]" class="form-control form-control-sm" placeholder="Unit"></td>
            <td><input type="number" step="0.0001" name="items[${rowIndex}][qty]" class="form-control form-control-sm boq-qty" value="0" placeholder="0"></td>
            <td><input type="number" step="0.0001" name="items[${rowIndex}][rate]" class="form-control form-control-sm boq-rate" value="0" placeholder="0"></td>
            <td><input type="text" name="items[${rowIndex}][rate_in_words]" class="form-control form-control-sm boq-rate-words" readonly placeholder="Auto"></td>
            <td><input type="text" class="form-control form-control-sm boq-amount" value="0.00" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeBoqRow(this)" title="Remove row"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        tr.querySelector('.boq-rate').addEventListener('input', updateRow);
        tr.querySelector('.boq-qty').addEventListener('input', updateRow);
        tr.querySelector('.boq-rate').addEventListener('blur', function() { updateRateInWords(this); });
    };

    window.removeBoqRow = function(btn) {
        const tbody = document.getElementById('boqItemsBody');
        if (tbody.querySelectorAll('tr.boq-row').length <= 1) return;
        btn.closest('tr').remove();
        renumberSn();
    };

    function renumberSn() {
        document.querySelectorAll('#boqItemsBody tr.boq-row').forEach((tr, i) => {
            tr.querySelector('.boq-sn').textContent = i + 1;
        });
    }

    function numToWords(num) {
        num = parseFloat(num) || 0;
        if (num === 0) return 'Zero';
        const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        const n = Math.floor(num);
        if (n < 20) return ones[n];
        if (n < 100) return tens[Math.floor(n/10)] + (n % 10 ? ' ' + ones[n%10] : '');
        if (n < 1000) return ones[Math.floor(n/100)] + ' Hundred' + (n % 100 ? ' ' + numToWords(n % 100) : '');
        if (n < 100000) return numToWords(Math.floor(n/1000)) + ' Thousand' + (n % 1000 ? ' ' + numToWords(n % 1000) : '');
        if (n < 10000000) return numToWords(Math.floor(n/100000)) + ' Lakh' + (n % 100000 ? ' ' + numToWords(n % 100000) : '');
        return numToWords(Math.floor(n/10000000)) + ' Crore' + (n % 10000000 ? ' ' + numToWords(n % 10000000) : '');
    }

    function updateRateInWords(input) {
        const rate = parseFloat(input.value) || 0;
        const row = input.closest('tr');
        const wordsInput = row.querySelector('.boq-rate-words');
        wordsInput.value = rate === 0 ? '' : numToWords(rate) + ' Only';
    }

    function updateRow(e) {
        const row = e.target.closest('tr');
        const qty = parseFloat(row.querySelector('.boq-qty').value) || 0;
        const rate = parseFloat(row.querySelector('.boq-rate').value) || 0;
        const amount = qty * rate;
        row.querySelector('.boq-amount').value = amount.toFixed(2);
        updateRateInWords(row.querySelector('.boq-rate'));
    }

    function updateRowFromInput(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.boq-qty').value) || 0;
        const rate = parseFloat(row.querySelector('.boq-rate').value) || 0;
        row.querySelector('.boq-amount').value = (qty * rate).toFixed(2);
    }

    document.getElementById('boqItemsBody').querySelectorAll('.boq-qty, .boq-rate').forEach(function(inp) {
        inp.addEventListener('input', function() {
            updateRowFromInput(this);
        });
        inp.addEventListener('blur', function() {
            if (this.classList.contains('boq-rate')) updateRateInWords(this);
        });
    });
})();
</script>
@endsection
