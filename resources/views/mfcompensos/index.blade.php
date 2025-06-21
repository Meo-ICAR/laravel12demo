@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('mfcompensos.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-2">
                    <input type="file" name="file" class="form-control-file" required>
                </div>
                <button type="submit" class="btn btn-primary">Import Excel/CSV</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('mfcompensos.index') }}" method="GET" class="row" id="filterForm">
                <div class="col-md-2">
                    <label for="stato">Filter by Stato:</label>
                    <select name="stato" id="stato" class="form-control">
                        <option value="">All Stati</option>
                        @foreach($statoOptions as $option)
                            <option value="{{ $option }}" {{ request('stato') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="denominazione_riferimento">Denominazione Riferimento:</label>
                    <input type="text" name="denominazione_riferimento" id="denominazione_riferimento"
                           class="form-control" value="{{ request('denominazione_riferimento') }}"
                           placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="istituto_finanziario">Istituto Finanziario:</label>
                    <input type="text" name="istituto_finanziario" id="istituto_finanziario"
                           class="form-control" value="{{ request('istituto_finanziario') }}"
                           placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="cognome">Cognome:</label>
                    <input type="text" name="cognome" id="cognome"
                           class="form-control" value="{{ request('cognome') }}"
                           placeholder="Search...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-info mr-2">Filter</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="clearFilters()">Clear</button>
                    <button type="button" class="btn btn-warning ml-2" id="bulkUpdateBtn">
                        Massive: Inserito → Proforma
                    </button>
                </div>
            </form>
            <form id="bulkUpdateForm" action="{{ route('mfcompensos.bulkUpdateToProforma') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="stato" id="bulk_stato">
                <input type="hidden" name="denominazione_riferimento" id="bulk_denominazione_riferimento">
                <input type="hidden" name="istituto_finanziario" id="bulk_istituto_finanziario">
                <input type="hidden" name="cognome" id="bulk_cognome">
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">MFCompensos List</h3>
                <div>
                    <a href="{{ route('mfcompensos.proformaSummary') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-chart-bar"></i> Proforma Summary
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Legacy ID</th>
                            <th>Data Inserimento Compenso</th>
                            <th>Descrizione</th>
                            <th>Tipo</th>
                            <th>Importo</th>
                            <th>Invoice Number</th>
                            <th>Importo Effettivo</th>
                            <th>Quota</th>
                            <th>Stato</th>
                            <th>Denominazione Riferimento</th>
                            <th>Entrata/Uscita</th>
                            <th>Cognome</th>
                            <th>Nome</th>
                            <th>Segnalatore</th>
                            <th>Fonte</th>
                            <th>ID Pratica</th>
                            <th>Tipo Pratica</th>
                            <th>Data Inserimento Pratica</th>
                            <th>Data Stipula</th>
                            <th>Istituto Finanziario</th>
                            <th>Prodotto</th>
                            <th>Macrostatus</th>
                            <th>Status Pratica</th>
                            <th>Data Status Pratica</th>
                            <th>Montante</th>
                            <th>Importo Erogato</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mfcompensos as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->legacy_id }}</td>
                                <td>{{ $item->data_inserimento_compenso }}</td>
                                <td>{{ $item->descrizione }}</td>
                                <td>{{ $item->tipo }}</td>
                                <td>{{ number_format($item->importo, 2, ',', '.') }}</td>
                                <td>{{ $item->invoice_number }}</td>
                                <td>{{ $item->importo_effettivo }}</td>
                                <td>{{ $item->quota }}</td>
                                <td>
                                    <select class="form-control form-control-sm stato-select" data-id="{{ $item->id }}" style="min-width: 100px;">
                                        @foreach($statoOptions as $option)
                                            <option value="{{ $option }}" {{ $item->stato == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>{{ $item->denominazione_riferimento }}</td>
                                <td>{{ $item->entrata_uscita }}</td>
                                <td>{{ $item->cognome }}</td>
                                <td>{{ $item->nome }}</td>
                                <td>{{ $item->segnalatore }}</td>
                                <td>{{ $item->fonte }}</td>
                                <td>{{ $item->id_pratica }}</td>
                                <td>{{ $item->tipo_pratica }}</td>
                                <td>{{ $item->data_inserimento_pratica }}</td>
                                <td>{{ $item->data_stipula }}</td>
                                <td>{{ $item->istituto_finanziario }}</td>
                                <td>{{ $item->prodotto }}</td>
                                <td>{{ $item->macrostatus }}</td>
                                <td>{{ $item->status_pratica }}</td>
                                <td>{{ $item->data_status_pratica }}</td>
                                <td>{{ $item->montante }}</td>
                                <td>{{ $item->importo_erogato }}</td>
                                <td>
                                    <a href="{{ route('mfcompensos.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="27" class="text-center">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $mfcompensos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter form submission - omit empty values from URL
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Get form data
            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Only add non-empty values to URL parameters
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value.trim());
                }
            }

            // Build the URL with only non-empty parameters
            const baseUrl = '{{ route("mfcompensos.index") }}';
            const queryString = params.toString();
            const finalUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

            // Navigate to the filtered URL
            window.location.href = finalUrl;
        });
    }

    // Handle inline stato changes
    document.querySelectorAll('.stato-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const newStato = this.value;
            const originalValue = this.getAttribute('data-original-value') || this.value;

            console.log('Updating stato for ID:', id, 'to:', newStato);

            // Send AJAX request to update stato
            fetch(`/mfcompensos/${id}`, {
                method: 'PUT',
                credentials: 'same-origin', // Include cookies/session
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    stato: newStato
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (response.redirected) {
                    // If redirected, likely to login page
                    window.location.href = response.url;
                    return;
                }
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data) return; // Already handled redirect

                console.log('Response data:', data);
                if (data.success) {
                    // Update the original value
                    this.setAttribute('data-original-value', newStato);

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Stato updated successfully!
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    `;

                    // Insert alert at the top of the container
                    const container = document.querySelector('.container-fluid');
                    if (container && container.firstChild) {
                        container.insertBefore(alert, container.firstChild);
                    } else if (container) {
                        container.appendChild(alert);
                    }

                    // Remove alert after 3 seconds
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 3000);
                } else {
                    console.error('Server returned error:', data);
                    alert('Error updating stato: ' + (data.message || 'Unknown error'));
                    // Revert to original value
                    this.value = originalValue;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error updating stato: ' + error.message);
                // Revert to original value
                this.value = originalValue;
            });
        });

        // Store original value when page loads
        select.setAttribute('data-original-value', select.value);
    });

    // Bulk update button
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    if (bulkUpdateBtn) {
        bulkUpdateBtn.addEventListener('click', function() {
            // Get current filter values
            const stato = document.getElementById('stato').value;
            const denominazione = document.getElementById('denominazione_riferimento').value;
            const istituto = document.getElementById('istituto_finanziario').value;
            const cognome = document.getElementById('cognome').value;

            // Populate hidden form fields
            document.getElementById('bulk_stato').value = stato;
            document.getElementById('bulk_denominazione_riferimento').value = denominazione;
            document.getElementById('bulk_istituto_finanziario').value = istituto;
            document.getElementById('bulk_cognome').value = cognome;

            console.log('Bulk update with filters:', { stato, denominazione, istituto, cognome });

            if (confirm('Are you sure you want to change stato from Inserito to Proforma for all filtered records?')) {
                document.getElementById('bulkUpdateForm').submit();
            }
        });
    }
});

// Function to clear all filters and navigate to base URL
function clearFilters() {
    // Clear all form fields
    document.getElementById('stato').value = '';
    document.getElementById('denominazione_riferimento').value = '';
    document.getElementById('istituto_finanziario').value = '';
    document.getElementById('cognome').value = '';

    // Navigate to base URL without any parameters
    window.location.href = '{{ route("mfcompensos.index") }}';
}
</script>
@endsection
