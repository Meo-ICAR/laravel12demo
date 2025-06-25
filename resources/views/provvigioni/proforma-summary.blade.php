@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Inserito Summary</h2>
            <p class="text-muted">Records with stato 'Inserito' grouped by Denominazione Riferimento</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Provvigioni
            </a>
        </div>
    </div>

    <!-- Sorting Controls -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label for="orderBy" class="mb-0"><strong>Sort by:</strong></label>
                            <select id="orderBy" class="form-control form-control-sm">
                                <option value="denominazione_riferimento" {{ $orderBy === 'denominazione_riferimento' ? 'selected' : '' }}>Denominazione Riferimento</option>
                                <option value="totale" {{ $orderBy === 'totale' ? 'selected' : '' }}>Total Amount</option>
                                <option value="n" {{ $orderBy === 'n' ? 'selected' : '' }}>Number of Records</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="orderDirection" class="mb-0"><strong>Direction:</strong></label>
                            <select id="orderDirection" class="form-control form-control-sm">
                                <option value="asc" {{ $orderDirection === 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ $orderDirection === 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="applySort" class="btn btn-primary btn-sm mt-4">
                                <i class="fas fa-sort"></i> Apply Sort
                            </button>
                        </div>
                        <div class="col-md-3 text-right">
                            <small class="text-muted">
                                Current: {{ ucfirst(str_replace('_', ' ', $orderBy)) }}
                                ({{ $orderDirection === 'asc' ? 'A-Z' : 'Z-A' }})
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Inserito Records Summary</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Denominazione Riferimento</th>
                            <th>Email</th>
                            <th class="text-right">N</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Contributo</th>
                            <th class="text-right">Anticipo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proformaSummary as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('provvigioni.index', ['stato' => 'Inserito', 'denominazione_riferimento' => $item->denominazione_riferimento]) }}"
                                       class="text-primary font-weight-bold">
                                        {{ $item->denominazione_riferimento ?: 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    @if($item->email)
                                        <a href="mailto:{{ $item->email }}" class="text-info">
                                            <i class="fas fa-envelope"></i> {{ $item->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-info">{{ $item->n }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-success font-weight-bold">
                                        € {{ number_format($item->totale, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span>{{ $item->contributo !== null ? number_format($item->contributo, 2, ',', '.') : '-' }}</span>
                                </td>
                                <td class="text-right">
                                    <span>{{ $item->anticipo !== null ? number_format($item->anticipo, 2, ',', '.') : '-' }}</span>
                                </td>
                                <td>
                                    <form action="{{ route('provvigioni.createProformaFromSummary') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="denominazione_riferimento" value="{{ $item->denominazione_riferimento }}">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-file-invoice"></i> Proforma
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                        <p class="text-muted">No Inserito records found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($proformaSummary->count() > 0)
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="4"><strong>TOTALS</strong></td>
                                <td class="text-right">
                                    <strong>€ {{ number_format($proformaSummary->sum('totale'), 2, ',', '.') }}</strong>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if($proformaSummary->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Summary Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $proformaSummary->count() }}</h4>
                                    <small class="text-muted">Unique Denominazioni</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success">{{ $proformaSummary->sum('n') }}</h4>
                                    <small class="text-muted">Total Records</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success">€ {{ number_format($proformaSummary->sum('totale'), 2, ',', '.') }}</h4>
                                    <small class="text-muted">Total Amount</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-info">€ {{ $proformaSummary->sum('n') > 0 ? number_format($proformaSummary->sum('totale') / $proformaSummary->sum('n'), 2, ',', '.') : '0,00' }}</h4>
                                    <small class="text-muted">Average per Record</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Content for <span id="emailDenominazione"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="emailContent">Email Content:</label>
                    <div id="emailContent" class="border p-3 bg-light" style="max-height: 400px; overflow-y: auto;" contenteditable="true"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i> Copy to Clipboard
                </button>
                <button type="button" class="btn btn-success" onclick="sendEmailAndUpdateRecords()">
                    <i class="fas fa-paper-plane"></i> Send & Update Records
                </button>
            </div>
        </div>
    </div>
</div>

<!-- All Emails Modal -->
<div class="modal fade" id="allEmailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">All Email Content</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="allEmailsContent" style="max-height: 600px; overflow-y: auto;">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyAllEmailsToClipboard()">
                    <i class="fas fa-copy"></i> Copy All to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle email button clicks
    document.querySelectorAll('.send-email-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const denominazione = this.getAttribute('data-denominazione');

            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            this.disabled = true;

            // Send AJAX request to generate email
            fetch('{{ route("provvigioni.sendProformaEmail") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    denominazione_riferimento: denominazione
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update modal content
                    document.getElementById('emailDenominazione').textContent = data.denominazione;
                    document.getElementById('emailContent').innerHTML = data.email_content;

                    // Show modal
                    $('#emailModal').modal('show');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                    // Still show the email content even if sending failed
                    if (data.email_content) {
                        document.getElementById('emailDenominazione').textContent = data.denominazione || 'Unknown';
                        document.getElementById('emailContent').innerHTML = data.email_content;
                        $('#emailModal').modal('show');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error generating email: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = '<i class="fas fa-envelope"></i> Email';
                this.disabled = false;
            });
        });
    });

    // Handle "Send Email to All" button
    document.getElementById('sendEmailToAllBtn').addEventListener('click', function() {
        // Show loading state
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating All Emails...';
        this.disabled = true;

        // Send AJAX request to generate all emails
        fetch('{{ route("provvigioni.sendAllProformaEmails") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create detailed results content
                let resultsContent = '<div class="mb-4">';

                // Show sent emails
                if (data.sent_emails && data.sent_emails.length > 0) {
                    resultsContent += '<div class="alert alert-success">';
                    resultsContent += '<h5><i class="fas fa-check-circle"></i> Successfully Sent (' + data.sent_emails.length + ')</h5>';
                    resultsContent += '<ul>';
                    data.sent_emails.forEach(email => {
                        resultsContent += '<li><strong>' + email.denominazione + '</strong> → ' + email.email + ' (' + email.records + ' records, €' + email.total.toFixed(2) + ')</li>';
                    });
                    resultsContent += '</ul></div>';
                }

                // Show failed emails
                if (data.failed_emails && data.failed_emails.length > 0) {
                    resultsContent += '<div class="alert alert-danger">';
                    resultsContent += '<h5><i class="fas fa-exclamation-triangle"></i> Failed to Send (' + data.failed_emails.length + ')</h5>';
                    resultsContent += '<ul>';
                    data.failed_emails.forEach(email => {
                        resultsContent += '<li><strong>' + email.denominazione + '</strong> → ' + (email.email || 'No email') + ' (Error: ' + email.error + ')</li>';
                    });
                    resultsContent += '</ul></div>';
                }

                resultsContent += '</div>';

                // Add the email content
                resultsContent += data.all_emails_content;

                // Update modal content
                document.getElementById('allEmailsContent').innerHTML = resultsContent;

                // Show modal
                $('#allEmailsModal').modal('show');
            } else {
                alert('Error generating emails: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating emails: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            this.innerHTML = '<i class="fas fa-envelope"></i> Send Email to All';
            this.disabled = false;
        });
    });

    // Handle "Mark as Received" buttons
    document.querySelectorAll('.mark-received-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const denominazione = this.getAttribute('data-denominazione');

            if (confirm('Mark all Proforma records for "' + denominazione + '" as received?')) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                this.disabled = true;

                fetch('{{ route("provvigioni.markAsReceived") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        denominazione_riferimento: denominazione
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Success: ' + data.message);
                        // Reload the page to show updated status
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    this.innerHTML = '<i class="fas fa-check"></i> Received';
                    this.disabled = false;
                });
            }
        });
    });

    // Handle "Mark as Paid" buttons
    document.querySelectorAll('.mark-paid-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const denominazione = this.getAttribute('data-denominazione');

            if (confirm('Mark all Proforma records for "' + denominazione + '" as paid?')) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                this.disabled = true;

                fetch('{{ route("provvigioni.markAsPaid") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        denominazione_riferimento: denominazione
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Success: ' + data.message);
                        // Reload the page to show updated status
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    this.innerHTML = '<i class="fas fa-euro-sign"></i> Paid';
                    this.disabled = false;
                });
            }
        });
    });

    // Handle "Sync Denominazioni" button
    document.getElementById('syncDenominazioniBtn').addEventListener('click', function() {
        if (confirm('This will add all missing Denominazione Riferimento values from provvigioni to the fornitori table. Continue?')) {
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
            this.disabled = true;

            fetch('{{ route("provvigioni.syncDenominazioni") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success: ' + data.details);
                    // Reload the page to show updated data
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = '<i class="fas fa-sync"></i> Sync Denominazioni';
                this.disabled = false;
            });
        }
    });

    // Handle sorting
    document.getElementById('applySort').addEventListener('click', function() {
        const orderBy = document.getElementById('orderBy').value;
        const orderDirection = document.getElementById('orderDirection').value;

        // Build URL with current parameters
        const url = new URL(window.location);
        url.searchParams.set('order_by', orderBy);
        url.searchParams.set('order_direction', orderDirection);

        // Navigate to the sorted view
        window.location.href = url.toString();
    });

    // Auto-apply sort when dropdowns change (optional)
    document.getElementById('orderBy').addEventListener('change', function() {
        document.getElementById('applySort').click();
    });

    document.getElementById('orderDirection').addEventListener('change', function() {
        document.getElementById('applySort').click();
    });
});

function copyToClipboard() {
    const contentDiv = document.getElementById('emailContent');
    const content = contentDiv.innerText || contentDiv.textContent;

    // Create temporary textarea
    const textarea = document.createElement('textarea');
    textarea.value = content;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    // Show success message
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}

function sendEmailAndUpdateRecords() {
    const denominazione = document.getElementById('emailDenominazione').textContent;
    const emailContent = document.getElementById('emailContent').innerHTML;

    if (confirm('This will mark all Inserito records for "' + denominazione + '" as sent with the current date and time and update their status to Proforma. Continue?')) {
        // Show loading state on the send button
        const sendBtn = event.target;
        const originalText = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        sendBtn.disabled = true;

        fetch('{{ route("provvigioni.markAsSent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                denominazione_riferimento: denominazione,
                email_content: emailContent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Success: ' + data.message);
                // Close modal and reload page to show updated status
                $('#emailModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            sendBtn.innerHTML = originalText;
            sendBtn.disabled = false;
        });
    }
}

function copyAllEmailsToClipboard() {
    const content = document.getElementById('allEmailsContent').innerText;

    // Create temporary textarea
    const textarea = document.createElement('textarea');
    textarea.value = content;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    // Show success message
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>
@endsection
