@php
    $fieldName = $name ?? 'payment_method';
    $fieldId = $id ?? $fieldName;
    $fieldClass = $class ?? 'form-select';
    $selected = (string) (old($fieldName, $selected ?? '') ?? '');
    $known = ['cash', 'bank_transfer', 'cheque', 'online_payment', 'other'];
    $showLegacy = $selected !== '' && ! in_array($selected, $known, true);
@endphp
<select name="{{ $fieldName }}" id="{{ $fieldId }}" class="{{ $fieldClass }}">
    <option value="">{{ $placeholder ?? 'Select Method' }}</option>
    @if ($showLegacy)
        <option value="{{ $selected }}" selected>{{ $selected }}</option>
    @endif
    <option value="cash" {{ $selected === 'cash' ? 'selected' : '' }}>Cash</option>
    <option value="bank_transfer" {{ $selected === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
    <option value="cheque" {{ $selected === 'cheque' ? 'selected' : '' }}>Cheque</option>
    <option value="online_payment" {{ $selected === 'online_payment' ? 'selected' : '' }}>Online Payment</option>
    <option value="other" {{ $selected === 'other' ? 'selected' : '' }}>Other</option>
</select>
