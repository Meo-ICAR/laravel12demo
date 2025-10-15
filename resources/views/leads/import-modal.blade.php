<div class="modal fade" id="importLeadsModal" tabindex="-1" role="dialog" aria-labelledby="importLeadsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLeadsModalLabel">Importa Leads da SIDIAL</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importLeadsForm" action="{{ route('leads.import.sidial') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="from_date">Data Inizio</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>
                    <div class="form-group">
                        <label for="to_date">Data Fine</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dry_run" name="dry_run">
                            <label class="form-check-label" for="dry_run">
                                Solo simulazione (non salva sul database)
                            </label>
                        </div>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Set default dates (last 7 days)
    let endDate = new Date();
    let startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };
    
    document.getElementById('from_date').value = formatDate(startDate);
    document.getElementById('to_date').value = formatDate(endDate);
    
    // Handle form submission
    $('#importLeadsForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            dry_run: $('#dry_run').is(':checked') ? 1 : 0
        };
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#importLeadsForm button[type="submit"]')
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
                $('#importLeadsModal').modal('hide');
                
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
                $('#importLeadsForm button[type="submit"]')
                    .prop('disabled', false)
                    .text('Importa');
            }
        });
    });
});
</script>
@endpush
