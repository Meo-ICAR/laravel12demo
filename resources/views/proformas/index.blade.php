@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Proformas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Proformas</li>
            </ol>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
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
                                <label for="sended_at">Sended At</label>
                                <input type="text" name="sended_at" id="sended_at" class="form-control" value="{{ request('sended_at') }}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="paid_at">Paid At</label>
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
                    <h3 class="card-title">Proformas List</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fornitore</th>
                                <th>Stato</th>
                                <th class="text-right">Compenso</th>
                                <th class="text-right">Contributo</th>
                                <th class="text-right">Anticipo</th>
                                <th class="text-right">Totale</th>
                                <th class="text-right">Provvigioni</th>
                                <th>Sended At</th>
                                <th>Paid At</th>
                                <th>Actions</th>
                                <th>Email Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proformas as $proforma)
                                <tr>
                                    <td>{{ $proforma->id }}</td>
                                    <td>{{ $proforma->fornitore->name ?? '-' }}</td>
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
                                        <div class="btn-group">
                                            <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$proforma->sended_at)
                                                <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('proformas.destroy', $proforma) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will restore associated provvigioni to \'Inserito\' status.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-warning btn-sm" disabled title="Cannot edit - email already sent">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" disabled title="Cannot delete - email already sent">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#emailBodyModal-{{ $proforma->id }}">
                                                <i class="fas fa-envelope-open-text"></i>
                                            </button>
                                        </div>
                                        <!-- Modal -->
                                        <div class="modal fade" id="emailBodyModal-{{ $proforma->id }}" tabindex="-1" role="dialog" aria-labelledby="emailBodyModalLabel-{{ $proforma->id }}" aria-hidden="true">
                                          <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h5 class="modal-title" id="emailBodyModalLabel-{{ $proforma->id }}">Email Body - Proforma #{{ $proforma->id }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                                </button>
                                              </div>
                                              <div class="modal-body">
                                                <label for="emailsubject_{{ $proforma->id }}"><strong>Email Subject:</strong></label>
                                                <input type="text" id="emailsubject_{{ $proforma->id }}" class="form-control mb-3" value="{{ $proforma->emailsubject ?? '' }}" placeholder="Enter email subject">

                                                <label for="compenso_descrizione_{{ $proforma->id }}"><strong>Compenso Descrizione (HTML):</strong></label>
                                                <textarea id="compenso_descrizione_{{ $proforma->id }}" class="form-control mb-3" rows="3">{!! $proforma->compenso_descrizione !!}</textarea>
                                                <button type="button" class="btn btn-primary mb-3" onclick="saveCompensoDescrizione('{{ $proforma->id }}')">
                                                    <i class="fas fa-save"></i> Salva Dati
                                                </button>
                                                <div id="modal-feedback-{{ $proforma->id }}" class="mt-2"></div>
                                                {!! $proforma->emailbody !!}

                                                <div class="mt-4">
                                                    <label for="annotation_{{ $proforma->id }}"><strong>Annotazioni interne non inviate:</strong></label>
                                                    <textarea id="annotation_{{ $proforma->id }}" class="form-control" rows="3" placeholder="Enter internal annotations">{{ $proforma->annotation ?? '' }}</textarea>
                                                </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-success" onclick="sendProformaEmail('{{ $proforma->id }}')">
                                                    <i class="fas fa-paper-plane"></i> Invia Email
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
    fetch('/proformas/' + proformaId + '/send-email', {
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
});
</script>
@endsection
