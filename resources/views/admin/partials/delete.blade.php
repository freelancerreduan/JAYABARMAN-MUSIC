{{-- Delete button with a confirm. Params: action, confirm, label (optional), class (optional) --}}
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm ?? 'Delete this? It cannot be undone.' }}" class="d-inline no-drag {{ $class ?? '' }}">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-sm btn-danger {{ empty($label) ? 'btn-icon' : '' }}" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i>@if (! empty($label)) {{ $label }}@endif</button>
</form>
