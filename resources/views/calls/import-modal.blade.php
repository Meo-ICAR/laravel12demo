<div class="modal" id="importEsitiModal" tabindex="-1" role="dialog" aria-labelledby="importEsitiModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importEsitiModalLabel">Importa Esiti da SIDIAL</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="document.getElementById('importEsitiModal').style.display='none';">
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
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('importEsitiModal').style.display='none';">Annulla</button>
                    <button type="submit" class="btn btn-primary">Importa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    background-color: rgba(0,0,0,0.5);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    display: none;
    overflow: auto;
}

.modal.show {
    display: block;
}

.modal-dialog {
    max-width: 500px;
    margin: 1.75rem auto;
}

.modal-content {
    position: relative;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: .3rem;
    outline: 0;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.modal-body {
    position: relative;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .5;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.close:hover {
    opacity: .75;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (last 7 days)
    let endDate = new Date();
    let startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    document.getElementById('from_date').value = formatDate(startDate);
    document.getElementById('to_date').value = formatDate(endDate);
    
    // Handle form submission
    document.getElementById('importEsitiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importazione in corso...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Show success message
            const noty = new Noty({
                type: 'success',
                text: data.message || 'Importazione completata con successo!',
                timeout: 5000
            });
            noty.show();
            
            // Close modal
            document.getElementById('importEsitiModal').style.display = 'none';
            
            // Reload the page after a short delay
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Si è verificato un errore durante l\'importazione';
            
            try {
                if (error.response) {
                    errorMessage = error.response.data.message || errorMessage;
                }
            } catch (e) {}
            
            const noty = new Noty({
                type: 'error',
                text: errorMessage,
                timeout: 5000
            });
            noty.show();
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Importa';
        });
    });
});
</script>
