<label class="block">
    <span class="form-label">{{ $label }}{{ ($required ?? false) ? ' *' : '' }}</span>
    <input name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ isset($fieldValue) ? $fieldValue($oldKey ?? $name) : old($oldKey ?? $name) }}" class="form-control"
        @required($required ?? false) @if(isset($max)) maxlength="{{ $max }}" @endif
        @if(isset($step)) step="{{ $step }}" @endif @if(isset($min)) min="{{ $min }}" @endif>
</label>
