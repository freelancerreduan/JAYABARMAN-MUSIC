{{-- Upload box. Params: name, accept, multiple (bool), title, hint, small (bool) --}}
@php $multiple = $multiple ?? false; @endphp
<div class="dz-wrap">
  <label @class(['dropzone', 'sm' => $small ?? false])>
    <i class="bi bi-cloud-arrow-up"></i>
    <strong>{{ $title ?? ($multiple ? 'Drag files here or click to choose' : 'Drag a file here or click to choose') }}</strong>
    @if (! empty($hint))<small>{{ $hint }}</small>@endif
    <span class="dz-count"></span>
    <input type="file" name="{{ $name }}{{ $multiple ? '[]' : '' }}" accept="{{ $accept ?? 'image/*' }}" @if ($multiple) multiple @endif @if (! empty($required)) required @endif @if (! empty($form)) form="{{ $form }}" @endif>
  </label>
  <div class="drop-picked"></div>
</div>
