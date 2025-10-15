<!-- Import Modal -->
<div class="modal fade" id="importEsitiModal" tabindex="-1" role="dialog" aria-labelledby="importEsitiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importEsitiModalLabel">Importa Esiti da SIDIAL</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importEsitiForm" action="{{ route('calls.import.sidial') }}" method="POST">
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

<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (last 7 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    // Set default date values
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    
    if (fromDateInput && toDateInput) {
        fromDateInput.value = formatDate(startDate);
        toDateInput.value = formatDate(endDate);
    }
    
    // Handle form submission with event delegation
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.id === 'importEsitiForm') {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                // Show loading state
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importazione in corso...';
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Show success message
                    if (window.Noty) {
                        new Noty({
                            type: 'success',
                            text: data.message || 'Importazione completata con successo!',
                            timeout: 5000
                        }).show();
                    } else {
                        alert(data.message || 'Importazione completata con successo!');
                    }
                    
                    // Close modal using Bootstrap's modal method if available
                    const modal = document.getElementById('importEsitiModal');
                    if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            modal.style.display = 'none';
                        }
                    } else if (modal) {
                        modal.style.display = 'none';
                    }
                    
                    // Reload the page after a short delay
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = 'Si è verificato un errore durante l\'importazione';
                    
                    if (window.Noty) {
                        new Noty({
                            type: 'error',
                            text: errorMessage,
                            timeout: 5000
                        }).show();
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    // Reset button state
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Importa';
                    }
                });
            }
        }
    });
});
</script>
