{{-- Delete button with a confirm. Params: action, confirm, btnLabel (optional), class (optional) --}}
<form method="POST" action="{{ $action }}" data-turbo-confirm="{{ $confirm ?? 'Delete this? It cannot be undone.' }}" class="d-inline no-drag {{ $class ?? '' }}">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-sm btn-danger {{ empty($btnLabel) ? 'btn-icon' : '' }}" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i>@if (! empty($btnLabel)) {{ $btnLabel }}@endif</button>
</form>
