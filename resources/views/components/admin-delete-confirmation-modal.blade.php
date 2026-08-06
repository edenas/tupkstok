@props([
    'title' => 'Ištrinti vartotoją',
    'message' => 'Ar tikrai norite ištrinti šį vartotoją?',
])

<div class="admin-modal" data-delete-confirmation-modal aria-hidden="true" hidden>
    <div class="admin-modal__backdrop" data-delete-confirmation-cancel></div>

    <section class="admin-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-modal-title">
        <h2 id="delete-confirmation-modal-title" class="admin-modal__title" data-delete-confirmation-title>{{ $title }}</h2>
        <p class="admin-modal__message" data-delete-confirmation-message>{{ $message }}</p>

        <div class="admin-modal__actions">
            <button type="button" class="admin-button admin-button--danger" data-delete-confirmation-submit>
                Taip, ištrinti
            </button>
            <button type="button" class="admin-button admin-button--secondary" data-delete-confirmation-cancel>
                Ne, atšaukti
            </button>
        </div>
    </section>
</div>
