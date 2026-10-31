<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: var(--sm-radius);">
            <div class="modal-body p-4 text-center">
                <div class="empty-icon mx-auto mb-3 tone-red d-inline-flex align-items-center justify-content-center rounded-circle" style="width:3.5rem;height:3.5rem;font-size:1.5rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 class="mb-2" id="confirmModalTitle" data-confirm-title>Are you sure?</h5>
                <p class="text-muted mb-4" data-confirm-message></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-confirm-accept>Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
