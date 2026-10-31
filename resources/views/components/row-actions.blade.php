@props(['show' => null, 'edit' => null, 'delete' => null, 'deleteMessage' => 'This record will be permanently deleted.'])

@if ($show || $edit || $delete)
    <div class="dropdown">
        <button class="btn btn-light btn-icon btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
            <i class="bi bi-three-dots"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @if ($show)
                <li><a class="dropdown-item" href="{{ $show }}"><i class="bi bi-eye me-2"></i>View</a></li>
            @endif
            @if ($edit)
                <li><a class="dropdown-item" href="{{ $edit }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
            @endif
            @if ($delete)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ $delete }}" data-confirm="{{ $deleteMessage }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
@endif
