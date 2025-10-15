<div class="modal fade" id="importPraticheModal" tabindex="-1" role="dialog" aria-labelledby="importPraticheModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importPraticheModalLabel">Importa Pratiche</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importPraticheForm" action="{{ route('pratiche.import') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="start_date">Data Inizio</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Data Fine</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Importa</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
    $(document).ready(function() {
        // Set default dates (last 7 days)
        let endDate = new Date();
        let startDate = new Date();
        startDate.setDate(startDate.getDate() - 7);
        
        document.getElementById('start_date').valueAsDate = startDate;
        document.getElementById('end_date').valueAsDate = endDate;
        
        // Handle form submission
        $('#importPraticheForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                beforeSend: function() {
                    // Show loading state
                    $('#importPraticheForm button[type="submit"]')
                        .prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importazione in corso...');
                },
                success: function(response) {
                    // Show success message
                    new Noty({
                        type: 'success',
                        text: response.message || 'Importazione completata con successo!',
                        timeout: 5000
                    }).show();
                    
                    // Close modal
                    $('#importPraticheModal').modal('hide');
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    let errorMessage = 'Si è verificato un errore durante l\'importazione';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    new Noty({
                        type: 'error',
                        text: errorMessage,
                        timeout: 5000
                    }).show();
                },
                complete: function() {
                    // Reset button state
                    $('#importPraticheForm button[type="submit"]')
                        .prop('disabled', false)
                        .text('Importa');
                }
            });
        });
    });
</script>
@endpush
