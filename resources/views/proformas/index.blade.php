@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Proforma</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Proforma</li>
            </ol>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Filtri</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="filterCollapse">
                    <form method="GET" action="" class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="fornitore">Fornitore</label>
                                <input type="text" name="fornitore" id="fornitore" class="form-control" value="{{ request('fornitore') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="stato">Stato</label>
                                <select name="stato" id="stato" class="form-control">
                                    <option value="">-- All --</option>
                                    <option value="Inserito" {{ request()->has('stato') ? (request('stato') == 'Inserito' ? 'selected' : '') : 'selected' }}>Inserito</option>
                                    <option value="Spedito" {{ request('stato') == 'Spedito' ? 'selected' : '' }}>Spedito</option>
                                    <option value="Fatturato" {{ request('stato') == 'Fatturato' ? 'selected' : '' }}>Fatturato</option>
                                    <option value="Difforme" {{ request('stato') == 'Difforme' ? 'selected' : '' }}>Difforme</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="emailsubject">Email Subject</label>
                                <input type="text" name="emailsubject" id="emailsubject" class="form-control" value="{{ request('emailsubject') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="sended_at">Inviato</label>
                                <input type="text" name="sended_at" id="sended_at" class="form-control" value="{{ request('sended_at') }}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="paid_at">Pagato</label>
                                <input type="text" name="paid_at" id="paid_at" class="form-control" value="{{ request('paid_at') }}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="compenso_min">Compenso Min</label>
                                <input type="number" step="0.01" name="compenso_min" class="form-control" value="{{ request('compenso_min') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="compenso_max">Compenso Max</label>
                                <input type="number" step="0.01" name="compenso_max" class="form-control" value="{{ request('compenso_max') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="totale_min">Totale Min</label>
                                <input type="number" step="0.01" name="totale_min" class="form-control" value="{{ request('totale_min') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="totale_max">Totale Max</label>
                                <input type="number" step="0.01" name="totale_max" class="form-control" value="{{ request('totale_max') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12 text-right">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <a href="{{ route('proformas.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista Proforma</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="sendBulkEmails()" id="bulkEmailBtn" disabled>
                            <i class="fas fa-envelope"></i> Invia tutte le Email
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>ID</th>
                                <th>Fornitore</th>
                                <th>Stato</th>
                                <th class="text-right">Compenso</th>
                                <th class="text-right">Contributo</th>
                                <th class="text-right">Anticipo</th>
                                <th class="text-right">Totale</th>
                                <th class="text-right">Provvigioni</th>
                                <th>Inviato</th>
                                <th>Pagato</th>
                                <th>Azioni</th>
                                <th>Email Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proformas as $proforma)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-proforma" data-id="{{ $proforma->id }}">
                                    </td>
                                    <td>{{ $proforma->id }}</td>
                                    <td>
                                        @if($proforma->fornitore)
                                            <a href="{{ route('provvigioni.index', [
                                                'denominazione_riferimento' => $proforma->fornitore->name,
                                                'sended_at' => $proforma->sended_at ? \Carbon\Carbon::parse($proforma->sended_at)->format('Y-m-d') : null,
                                                'stato_include' => 'Proforma,Fatturato,Pagato',
                                            ]) }}">
                                                {{ $proforma->fornitore->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $proforma->stato == 'Inserito' ? 'secondary' : ($proforma->stato == 'Proforma' ? 'info' : ($proforma->stato == 'Fatturato' ? 'warning' : ($proforma->stato == 'Pagato' ? 'success' : 'danger'))) }}">
                                            {{ $proforma->stato }}
                                        </span>
                                    </td>
                                    <td class="text-right">€ {{ number_format($proforma->compenso, 2, ',', '.') }}</td>
                                    <td class="text-right">€ {{ number_format($proforma->contributo ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-right">€ {{ number_format($proforma->anticipo ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-right">
                                        <strong class="text-{{ ($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0)) >= 0 ? 'success' : 'danger' }}">
                                            € {{ number_format($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0), 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-info">{{ $proforma->provvigioni->count() }}</span>
                                    </td>
                                    <td>{{ $proforma->sended_at ? \Carbon\Carbon::parse($proforma->sended_at)->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $proforma->paid_at ? \Carbon\Carbon::parse($proforma->paid_at)->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                              <form action="{{ route('proformas.destroy', $proforma) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will restore associated provvigioni to \'Inserito\' status.')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <!-- Email Simulation Modal -->
                                        <div class="modal fade" id="emailSimModal-{{ $proforma->id }}" tabindex="-1" role="dialog" aria-labelledby="emailSimModalLabel-{{ $proforma->id }}" aria-hidden="true">
                                          <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h5 class="modal-title" id="emailSimModalLabel-{{ $proforma->id }}">Simulated Email - Proforma #{{ $proforma->id }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                                </button>
                                              </div>
                                              <div class="modal-body">
                                                <div class="mb-2"><strong>To:</strong> {{ $proforma->emailto }}</div>
                                                <div class="mb-2"><strong>Subject:</strong> {{ $proforma->emailsubject }}</div>
                                                <hr>
                                                <div class="email-body">
                                                    @if($proforma->compenso_descrizione)
                                                        <div class="mb-2">{!! $proforma->compenso_descrizione !!}</div>
                                                    @endif
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Cognome</th>
                                                                    <th>Nome</th>
                                                                    <th>Descrizione</th>
                                                                    <th>Prodotto</th>
                                                                    <th class="text-right">Importo</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php $totalImporto = 0; @endphp
                                                                @foreach($proforma->provvigioni as $provvigione)
                                                                    <tr>
                                                                        <td>{{ $provvigione->cognome }}</td>
                                                                        <td>{{ $provvigione->nome }}</td>
                                                                        <td>{{ $provvigione->descrizione }}</td>
                                                                        <td>{{ $provvigione->prodotto }}</td>
                                                                        <td class="text-right">€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                    @php $totalImporto += $provvigione->importo; @endphp
                                                                @endforeach
                                                                @if($proforma->contributo > 0)
                                                                    <tr>
                                                                        <td colspan="4"><strong>{{ $proforma->contributo_descrizione ?? 'Contributo' }}</strong></td>
                                                                        <td class="text-right">€ {{ number_format($proforma->contributo, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endif
                                                                @if($proforma->anticipo > 0)
                                                                    <tr>
                                                                        <td colspan="4"><strong>{{ $proforma->anticipo_descrizione ?? 'Anticipo' }}</strong></td>
                                                                        <td class="text-right text-danger">€ {{ number_format($proforma->anticipo, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endif
                                                                @if(($proforma->anticipo + $proforma->contributo) > 0)
                                                                    <tr class="table-info">
                                                                        <td colspan="4" class="text-right"><strong>Totale</strong></td>
                                                                        <td class="text-right"><strong>€ {{ number_format($proforma->compenso + $proforma->contributo - $proforma->anticipo, 2, ',', '.') }}</strong></td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" onclick="copyEmailContent('{{ $proforma->id }}')">
                                                    <i class="fas fa-copy mr-1"></i> Copy Email Content
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    </td>
                                    <td>{{ $proforma->emailsubject ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                            <p class="text-muted">No proformas found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $proformas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('#filterCollapse form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Remove empty fields before submit
            Array.from(this.elements).forEach(function(el) {
                if ((el.tagName === 'INPUT' || el.tagName === 'SELECT') && !el.value) {
                    el.disabled = true;
                }
            });
        });
    }
});

function saveCompensoDescrizione(proformaId) {
    const textarea = document.getElementById('compenso_descrizione_' + proformaId);
    const emailsubject = document.getElementById('emailsubject_' + proformaId);
    const annotation = document.getElementById('annotation_' + proformaId);
    const feedback = document.getElementById('modal-feedback-' + proformaId);
    feedback.innerHTML = '';
    fetch('/proformas/' + proformaId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            compenso_descrizione: textarea.value,
            emailsubject: emailsubject.value,
            annotation: annotation.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors) {
            feedback.innerHTML = '<div class="alert alert-danger">' + Object.values(data.errors).join('<br>') + '</div>';
        } else {
            feedback.innerHTML = '<div class="alert alert-success">Dati salvati!</div>';
        }
    })
    .catch(() => {
        feedback.innerHTML = '<div class="alert alert-danger">Errore salvataggio.</div>';
    });
}

function sendProformaEmail(proformaId) {
    const feedback = document.getElementById('modal-feedback-' + proformaId);
    feedback.innerHTML = '';
    fetch('/proformas/' + proformaId + '/send-proforma-email', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            feedback.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
        } else {
            feedback.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Errore invio email.') + '</div>';
        }
    })
    .catch(() => {
        feedback.innerHTML = '<div class="alert alert-danger">Errore invio email.</div>';
    });
}

// Add CSS to style anticipo amount in red
const style = document.createElement('style');
style.textContent = `
    .anticipo-red {
        color: #dc3545 !important;
        font-weight: bold !important;
    }
`;
document.head.appendChild(style);

// Function to style anticipo amounts in modal
function styleAnticipoAmounts(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Find all text nodes that contain "Anticipo" or anticipo amounts
    const walker = document.createTreeWalker(
        modal,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );

    let node;
    while (node = walker.nextNode()) {
        const text = node.textContent;
        if (text.includes('Anticipo') || (text.includes('€') && text.includes('anticipo'))) {
            // Create a span to wrap the text
            const span = document.createElement('span');
            span.className = 'anticipo-red';
            span.textContent = text;
            node.parentNode.replaceChild(span, node);
        }
    }

    // Also target table cells that might contain anticipo amounts
    const tableCells = modal.querySelectorAll('td');
    tableCells.forEach(cell => {
        const text = cell.textContent;
        if (text.includes('Anticipo') || (text.includes('€') && text.toLowerCase().includes('anticipo'))) {
            cell.classList.add('anticipo-red');
        }
    });
}

// Apply styling when modal is shown
document.addEventListener('DOMContentLoaded', function() {
    // Listen for modal show events
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        if (modal.id && modal.id.startsWith('emailBodyModal-')) {
            styleAnticipoAmounts(modal.id);
        }
    });

    // Add event listeners for individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-proforma')) {
            updateBulkEmailButton();
        }
    });
});

// Function to toggle select all checkboxes
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const proformaCheckboxes = document.querySelectorAll('.select-proforma');

    proformaCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateBulkEmailButton();
}

// Function to update bulk email button state
function updateBulkEmailButton() {
    const selectedCheckboxes = document.querySelectorAll('.select-proforma:checked');
    const bulkEmailBtn = document.getElementById('bulkEmailBtn');

    if (selectedCheckboxes.length > 0) {
        bulkEmailBtn.disabled = false;
        bulkEmailBtn.textContent = `Send Bulk Emails (${selectedCheckboxes.length})`;
    } else {
        bulkEmailBtn.disabled = true;
        bulkEmailBtn.innerHTML = '<i class="fas fa-envelope"></i> Send Bulk Emails';
    }
}

// Function to send bulk emails
function sendBulkEmails() {
    const selectedCheckboxes = document.querySelectorAll('.select-proforma:checked');

    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one proforma to send emails.');
        return;
    }

    const proformaIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.dataset.id);

    if (!confirm(`Are you sure you want to send emails to ${proformaIds.length} proforma(s)?`)) {
        return;
    }

    // Show loading state
    const bulkEmailBtn = document.getElementById('bulkEmailBtn');
    const originalText = bulkEmailBtn.innerHTML;
    bulkEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    bulkEmailBtn.disabled = true;

    fetch('/proformas/send-bulk-emails', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            proforma_ids: proformaIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message with details
            let message = data.message + '\n\n';
            if (data.results) {
                data.results.forEach(result => {
                    const status = result.success ? '✅' : '❌';
                    message += `${status} Proforma #${result.proforma_id}: ${result.message}\n`;
                });
            }
            alert(message);

            // Refresh the page to show updated sended_at timestamps
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending bulk emails.');
    })
    .finally(() => {
        // Restore button state
        bulkEmailBtn.innerHTML = originalText;
        bulkEmailBtn.disabled = false;
        updateBulkEmailButton();
    });
}

// Function to copy email content to clipboard
function copyEmailContent(proformaId) {
    const modal = document.getElementById('emailSimModal-' + proformaId);
    if (!modal) {
        console.error('Modal not found');
        return;
    }

    // Get the email content elements
    const toElement = modal.querySelector('.modal-body div:first-child');
    const subjectElement = modal.querySelector('.modal-body div:nth-child(2)');
    const emailBodyElement = modal.querySelector('.email-body');

    if (!toElement || !subjectElement || !emailBodyElement) {
        console.error('Email content elements not found');
        return;
    }

    // Create a formatted text version of the email
    let emailText = '';

    // Add To field
    emailText += toElement.textContent.trim() + '\n';

    // Add Subject field
    emailText += subjectElement.textContent.trim() + '\n';

    // Add separator
    emailText += '─'.repeat(50) + '\n\n';

    // Add email body content
    const emailBodyClone = emailBodyElement.cloneNode(true);

    // Remove any script tags and style tags for clean text
    const scripts = emailBodyClone.querySelectorAll('script, style');
    scripts.forEach(script => script.remove());

    // Convert HTML table to plain text
    const tables = emailBodyClone.querySelectorAll('table');
    tables.forEach(table => {
        const rows = table.querySelectorAll('tr');
        let tableText = '';

        rows.forEach((row, index) => {
            const cells = row.querySelectorAll('th, td');
            const rowText = Array.from(cells).map(cell => {
                let cellText = cell.textContent.trim();
                // Add padding for better formatting
                return cellText.padEnd(20);
            }).join(' | ');
            tableText += rowText + '\n';

            // Add separator after header row
            if (index === 0) {
                tableText += '─'.repeat(rowText.length) + '\n';
            }
        });

        // Replace table with text version
        const tableWrapper = document.createElement('div');
        tableWrapper.textContent = tableText;
        table.parentNode.replaceChild(tableWrapper, table);
    });

    // Get the final text content
    emailText += emailBodyClone.textContent.trim();

    // Copy to clipboard
    navigator.clipboard.writeText(emailText).then(function() {
        // Show success feedback
        const copyButton = modal.querySelector('button[onclick*="copyEmailContent"]');
        if (copyButton) {
            const originalText = copyButton.innerHTML;
            copyButton.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
            copyButton.classList.remove('btn-primary');
            copyButton.classList.add('btn-success');

            setTimeout(function() {
                copyButton.innerHTML = originalText;
                copyButton.classList.remove('btn-success');
                copyButton.classList.add('btn-primary');
            }, 2000);
        }
    }).catch(function(err) {
        console.error('Failed to copy email content: ', err);
        alert('Failed to copy email content to clipboard');
    });
}
</script>
@endsection
