@extends('layouts.admin')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div id="provvigioni-alert-container"></div>
    <!-- Filter Section - First and Collapsed -->
    <div class="card mb-3">
        <div class="card-header" id="filterHeader" style="cursor: pointer;" data-toggle="collapse" data-target="#filterBody" aria-expanded="false" aria-controls="filterBody">
            <h5 class="mb-0">
                <i class="fas fa-filter mr-2"></i>
                Filters
                <i class="fas fa-chevron-down float-right" id="filterIcon"></i>
            </h5>
        </div>
        <div class="collapse" id="filterBody" aria-labelledby="filterHeader">
            <div class="card-body">
                <form action="{{ route('provvigioni.index') }}" method="GET" class="row" id="filterForm">
                    <div class="col-md-2">
                        <label for="stato">Filter by Stato:</label>
                        <select name="stato" id="stato" class="form-control">
                              <option value="">All</option>
                              @foreach($statoOptions as $option)
                                <option value="{{ $option }}" {{ request('stato') == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="denominazione_riferimento">Fornitore Name:</label>
                        <input type="text" name="denominazione_riferimento" id="denominazione_riferimento"
                               class="form-control" value="{{ request('denominazione_riferimento') }}"
                               placeholder="Search fornitore name...">
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

                    <div class="col-md-2">
                        <label for="sended_at">Sended At Date:</label>
                        <input type="date" name="sended_at" id="sended_at"
                               class="form-control" value="{{ request('sended_at') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="entrata_uscita">Entrata/Uscita:</label>
                        <select name="entrata_uscita" id="entrata_uscita" class="form-control" onchange="document.getElementById('filterForm').submit();">
                            <option value="" {{ request('entrata_uscita') == '' ? 'selected' : '' }}>All</option>
                            <option value="Entrata" {{ request('entrata_uscita') == 'Entrata' ? 'selected' : '' }}>Entrata</option>
                            <option value="Uscita" {{ request('entrata_uscita') == 'Uscita' ? 'selected' : '' }}>Uscita</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_pratica">Stato Pratica:</label>
                        <select name="status_pratica" id="status_pratica" class="form-control">
                            <option value="">All</option>
                            @php
                                $allStatusPratica = [
                                    'PERFEZIONATA',
                                    'CARICATA BANCA',
                                    'Inserita',
                                    'RICHIESTA EMISSIONE',
                                    'PRATICA RESPINTA',
                                    'DECLINATA',
                                    'RINUNCIA CLIENTE',
                                    'SOSPESA',
                                    'Richiesta Polizza',
                                    'NOTIFICA',
                                    'ATTO FISSATO',
                                    'PERIZIA KO',
                                    'DELIBERATA',
                                    'RIENTRO BENESTARE',
                                    'RIENTRO POLIZZA',
                                    'LIQUIDATA',
                                    'PERIZIA OK',
                                    'INVIO IN ISTRUTTORIA',
                                ];
                                $altroStatus = collect($allStatusPratica)->filter(function($v) {
                                    return $v && $v !== 'PERFEZIONATA';
                                });
                            @endphp
                            @foreach($altroStatus as $status)
                                <option value="{{ $status }}" {{ request('status_pratica') == $status ? 'selected' : '' }}>&nbsp;&nbsp;{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-info mr-2">Filter</button>
                        <button type="button" class="btn btn-secondary ml-2" onclick="clearFilters()">Clear</button>
                        <a href="{{ route('provvigioni.index') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-times mr-1"></i> Clear All
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Monthly Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        This Month ({{ now()->startOfMonth()->format('d/m/Y') }} - {{ now()->format('d/m/Y') }})
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($currentMonthCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($currentMonthTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-info">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Last Month ({{ now()->subMonth()->startOfMonth()->format('d/m/Y') }} - {{ now()->subMonth()->endOfMonth()->format('d/m/Y') }})
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($lastMonthCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($lastMonthTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <!-- Income (Entrata) Card -->
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        <i class="fas fa-arrow-down mr-2"></i> Income (Entrata)
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($incomeCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($incomeImporto, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <!-- Costs (Uscita/Other) Card -->
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">
                        <i class="fas fa-arrow-up mr-2"></i> Costs (Uscita/Other)
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($costCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($costImporto, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">
                        Summary
                        @if(request()->has('stato') || request()->has('denominazione_riferimento') || request()->has('istituto_finanziario') || request()->has('cognome') || request()->has('data_status_pratica_from') || request()->has('data_status_pratica_to') || request()->has('sended_at'))
                            <span class="badge badge-info ml-2">Filtered Results</span>
                        @endif
                    </h5>
                    <p class="card-text">
                        <strong>Total Records:</strong> {{ number_format($totalCount) }} |
                        <strong>Total Importo:</strong> € {{ number_format($totalImporto, 2, ',', '.') }}
                    </p>
                    @if(request()->has('stato') || request()->has('denominazione_riferimento') || request()->has('istituto_finanziario') || request()->has('cognome') || request()->has('data_status_pratica_from') || request()->has('data_status_pratica_to') || request()->has('sended_at'))
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-filter mr-1"></i>
                                Active filters:
                                @if(request('stato')) <span class="badge badge-secondary">Stato: {{ request('stato') }}</span> @endif
                                @if(request('denominazione_riferimento')) <span class="badge badge-secondary">Fornitore: {{ request('denominazione_riferimento') }}</span> @endif
                                @if(request('istituto_finanziario')) <span class="badge badge-secondary">Istituto: {{ request('istituto_finanziario') }}</span> @endif
                                @if(request('cognome')) <span class="badge badge-secondary">Cognome: {{ request('cognome') }}</span> @endif
                                @if(request('status_pratica')) <span class="badge badge-secondary">Status Pratica: {{ request('status_pratica') }}</span> @endif
                                @if(request('sended_at')) <span class="badge badge-secondary">Sended At: {{ request('sended_at') }}</span> @endif
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-info">
                                <i class="fas fa-chart-line mr-1"></i>
                                Showing {{ number_format($totalCount) }} of {{ number_format($totalUnfilteredCount) }} total records
                                ({{ $totalUnfilteredCount > 0 ? number_format(($totalCount / $totalUnfilteredCount) * 100, 1) : 0 }}%) |
                                € {{ number_format($totalImporto, 2, ',', '.') }} of € {{ number_format($totalUnfilteredImporto, 2, ',', '.') }} total importo
                                ({{ $totalUnfilteredImporto > 0 ? number_format(($totalImporto / $totalUnfilteredImporto) * 100, 1) : 0 }}%)
                            </small>
                        </p>
                    @endif
                </div>
                <div class="col-md-6 text-right">
                    <p class="card-text">
                        <small class="text-muted">Showing {{ $provvigioni->firstItem() ?? 0 }} to {{ $provvigioni->lastItem() ?? 0 }} of {{ $totalCount }} entries</small>
                    </p>

                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Provvigioni List</h3>
                <div>

                    <a href="{{ route('provvigioni.dashboard') }}" class="btn btn-info btn-sm mr-2">
                        <i class="fas fa-chart-line mr-1"></i> Dashboard
                    </a>
                    <a href="{{ route('provvigioni.proformaSummary') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-chart-bar mr-1"></i> Proforma Summary
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">
                                <input type="checkbox" id="provvigioni-toggle-all">
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'denominazione_riferimento', 'order' => request('sort') == 'denominazione_riferimento' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Fornitore
                                    @if(request('sort') == 'denominazione_riferimento')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-right">
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'importo', 'order' => request('sort') == 'importo' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Importo
                                    @if(request('sort') == 'importo')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'stato', 'order' => request('sort') == 'stato' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Stato
                                    @if(request('sort') == 'stato')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'cognome', 'order' => request('sort') == 'cognome' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Cognome
                                    @if(request('sort') == 'cognome')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'nome', 'order' => request('sort') == 'nome' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Nome
                                    @if(request('sort') == 'nome')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'tipo', 'order' => request('sort') == 'tipo' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Tipo
                                    @if(request('sort') == 'tipo')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'istituto_finanziario', 'order' => request('sort') == 'istituto_finanziario' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Istituto Finanziario
                                    @if(request('sort') == 'istituto_finanziario')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'status_pratica', 'order' => request('sort') == 'status_pratica' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Status
                                    @if(request('sort') == 'status_pratica')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('provvigioni.index', array_merge(request()->query(), ['sort' => 'sended_at', 'order' => request('sort') == 'sended_at' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none sortable-header">
                                    Sended At
                                    @if(request('sort') == 'sended_at')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Invoice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($provvigioni as $item)
                            <tr>
                                <td class="text-center align-middle">
                                    <input type="checkbox"
                                        class="provvigione-toggle-stato"
                                        data-id="{{ $item->id }}"
                                        @if($item->stato == 'Inserito') checked @endif
                                        @if($item->stato != 'Inserito' && $item->stato != 'Sospeso') disabled @endif
                                    >
                                </td>
                                <td>
                                    @if($item->fornitore_name)
                                        <a href="{{ route('fornitoris.index', ['name' => $item->fornitore_name]) }}">
                                            {{ $item->fornitore_name }}
                                        </a>
                                    @elseif($item->denominazione_riferimento)
                                        <span class="text-muted">{{ $item->denominazione_riferimento }} (COGE)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($item->importo, 2, ',', '.') }}</td>
                                <td>
                                    <select class="form-control form-control-sm stato-select"
                                            data-id="{{ $item->id }}"
                                            name="stato_{{ $item->id }}"
                                            id="stato_{{ $item->id }}"
                                            style="min-width: 100px;"
                                            @if($item->received_at) disabled @endif>
                                        @foreach($statoOptions as $option)
                                            <option value="{{ $option }}" {{ $item->stato == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>{{ $item->cognome }}</td>
                                <td>{{ $item->nome }}</td>
                                <td>{{ Str::limit($item->tipo, 20) }}</td>
                                <td>
                                    @if($item->istituto_finanziario)
                                        <a href="{{ route('clientis.index', ['name' => $item->istituto_finanziario]) }}">
                                            {{ $item->istituto_finanziario }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                {{ $item->status_pratica }}
                                </td>
                                <td>{{ $item->sended_at ? \Carbon\Carbon::parse($item->sended_at)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ Str::limit($item->invoice_number, 6) ?: 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">

                                        <a href="{{ route('provvigioni.show', $item->id) }}" class="btn btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $provvigioni->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter section collapse functionality
    const filterHeader = document.getElementById('filterHeader');
    const filterIcon = document.getElementById('filterIcon');
    const filterBody = document.getElementById('filterBody');

    if (filterHeader && filterIcon && filterBody) {
        // Ensure filter section starts collapsed (remove auto-expand for active filters)
        filterBody.classList.remove('show');
        filterIcon.style.transform = 'rotate(0deg)';

        // Add smooth transition for icon
        filterIcon.style.transition = 'transform 0.3s ease';

        // Listen for Bootstrap collapse events
        filterBody.addEventListener('show.bs.collapse', function() {
            filterIcon.style.transform = 'rotate(180deg)';
        });

        filterBody.addEventListener('hide.bs.collapse', function() {
            filterIcon.style.transform = 'rotate(0deg)';
        });

        // Also handle the click event for manual control
        filterHeader.addEventListener('click', function(e) {
            // Let Bootstrap handle the collapse, we just need to handle the icon
            setTimeout(() => {
                if (filterBody.classList.contains('show')) {
                    filterIcon.style.transform = 'rotate(180deg)';
                } else {
                    filterIcon.style.transform = 'rotate(0deg)';
                }
            }, 10);
        });
    }

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
            const baseUrl = '{{ route("provvigioni.index") }}';
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
            fetch(`/provvigioni/${id}/stato`, {
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

    // Handle stato toggle via checkbox
    function updateStatoCheckbox(checkbox, newStato) {
        // Also update the select if present in the same row
        const row = checkbox.closest('tr');
        if (row) {
            const select = row.querySelector('.stato-select');
            if (select) {
                select.value = newStato;
            }
        }
    }
    document.querySelectorAll('.provvigione-toggle-stato').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const checked = this.checked;
            const newStato = checked ? 'Inserito' : 'Sospeso';
            this.disabled = true;
            fetch(`/provvigioni/${id}/toggle-stato`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ stato: newStato })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatoCheckbox(this, newStato);
                    showProvvigioniAlert('success', 'Stato updated successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                    this.checked = !checked; // revert
                }
            })
            .catch(() => {
                showProvvigioniAlert('danger', 'Network error');
                this.checked = !checked; // revert
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Global toggle
    const globalToggle = document.getElementById('provvigioni-toggle-all');
    if (globalToggle) {
        globalToggle.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.provvigione-toggle-stato');
            let affected = 0;
            let completed = 0;
            let totalToAffect = 0;
            let errors = 0;
            // First, count how many will be affected
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.disabled && checkbox.checked !== globalToggle.checked) {
                    totalToAffect++;
                }
            });
            if (totalToAffect === 0) {
                showProvvigioniAlert('info', 'No records to update.');
                return;
            }
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.disabled && checkbox.checked !== globalToggle.checked) {
                    checkbox.checked = globalToggle.checked;
                    // Wrap the AJAX logic to count completions
                    const id = checkbox.getAttribute('data-id');
                    const checked = checkbox.checked;
                    const newStato = checked ? 'Inserito' : 'Sospeso';
                    checkbox.disabled = true;
                    fetch(`/provvigioni/${id}/toggle-stato`, {
                        method: 'PUT',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ stato: newStato })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateStatoCheckbox(checkbox, newStato);
                            affected++;
                        } else {
                            checkbox.checked = !checked;
                            errors++;
                        }
                    })
                    .catch(() => {
                        checkbox.checked = !checked;
                        errors++;
                    })
                    .finally(() => {
                        checkbox.disabled = false;
                        completed++;
                        if (completed === totalToAffect) {
                            if (affected > 0) {
                                showProvvigioniAlert('success', `${affected} record${affected === 1 ? '' : 's'} updated successfully!`);
                            }
                            if (errors > 0) {
                                showProvvigioniAlert('danger', `${errors} record${errors === 1 ? '' : 's'} failed to update.`);
                            }
                        }
                    });
                }
            });
        });
    }

    // Function to clear all filters and navigate to base URL
    function clearFilters() {
        try {
            console.log('clearFilters function called');

            // Clear all form fields with error handling
            const fields = [
                'stato',
                'denominazione_riferimento',
                'istituto_finanziario',
                'cognome',
                'data_status_pratica_from',
                'data_status_pratica_to',
                'sended_at'
            ];

            fields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) {
                    if (element.tagName === 'SELECT') {
                        element.selectedIndex = 0; // Reset select to first option
                    } else {
                        element.value = ''; // Clear input fields
                    }
                    console.log(`Cleared field: ${fieldId}`);
                } else {
                    console.warn(`Field not found: ${fieldId}`);
                }
            });

            // Navigate to base URL without any parameters
            const baseUrl = '{{ route("provvigioni.index") }}';
            console.log('Navigating to:', baseUrl);
            window.location.href = baseUrl;

        } catch (error) {
            console.error('Error in clearFilters:', error);
            // Fallback: just navigate to base URL
            window.location.href = '{{ route("provvigioni.index") }}';
        }
    }

    function showProvvigioniAlert(type, message) {
        const container = document.getElementById('provvigioni-alert-container');
        if (!container) return;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        `;
        container.appendChild(alert);
        setTimeout(() => {
            if (alert.parentNode) alert.parentNode.removeChild(alert);
        }, 3000);
    }

    // Function to change checked provvigioni from Sospeso to Inserito
    function changeSospesoToInserito() {
        try {
            alert('Function called!'); // Test if function is being called
            console.log('changeSospesoToInserito function called');

            // Get all provvigioni with "Sospeso" status (not just checked ones)
            const allRows = document.querySelectorAll('tbody tr');
            console.log('Total rows found:', allRows.length);

            const sospesoRows = Array.from(allRows).filter(row => {
                const select = row.querySelector('.stato-select');
                const isSospeso = select && select.value === 'Sospeso';
                if (isSospeso) {
                    console.log('Found Sospeso row:', row);
                }
                return isSospeso;
            });

            console.log('Sospeso rows found:', sospesoRows.length);

            if (sospesoRows.length === 0) {
                console.log('No Sospeso rows found, showing alert');
                showProvvigioniAlert('info', 'No provvigioni found with "Sospeso" status.');
                return;
            }

            const confirmMessage = `Are you sure you want to change ALL ${sospesoRows.length} provvigioni from "Sospeso" to "Inserito"?`;
            console.log('Showing confirmation:', confirmMessage);

            if (!confirm(confirmMessage)) {
                console.log('User cancelled the operation');
                return;
            }

            console.log('User confirmed, starting update process');

            // Show loading state
            const changeStatusBtn = document.getElementById('changeStatusBtn');
            const originalText = changeStatusBtn.innerHTML;
            changeStatusBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Updating...';
            changeStatusBtn.disabled = true;

            let completed = 0;
            let successCount = 0;
            let errorCount = 0;

            sospesoRows.forEach((row, index) => {
                const checkbox = row.querySelector('.provvigione-toggle-stato');
                const id = checkbox.getAttribute('data-id');

                console.log(`Processing row ${index + 1}/${sospesoRows.length}, ID: ${id}`);

                // Disable the checkbox during update
                if (checkbox) {
                    checkbox.disabled = true;
                }

                fetch(`/provvigioni/${id}/toggle-stato`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stato: 'Inserito' })
                })
                .then(response => {
                    console.log(`Response for ID ${id}:`, response);
                    return response.json();
                })
                .then(data => {
                    console.log(`Data for ID ${id}:`, data);
                    if (data.success) {
                        // Update the checkbox and select
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                        const select = row.querySelector('.stato-select');
                        if (select) {
                            select.value = 'Inserito';
                        }
                        successCount++;
                        console.log(`Success for ID ${id}, total success: ${successCount}`);
                    } else {
                        errorCount++;
                        console.log(`Error for ID ${id}, total errors: ${errorCount}`);
                    }
                })
                .catch((error) => {
                    console.error(`Fetch error for ID ${id}:`, error);
                    errorCount++;
                })
                .finally(() => {
                    // Re-enable the checkbox
                    if (checkbox) {
                        checkbox.disabled = false;
                    }
                    completed++;
                    console.log(`Completed ${completed}/${sospesoRows.length}`);

                    if (completed === sospesoRows.length) {
                        console.log('All operations completed. Success:', successCount, 'Errors:', errorCount);
                        // Show final results
                        if (successCount > 0) {
                            showProvvigioniAlert('success', `${successCount} provvigioni successfully changed from "Sospeso" to "Inserito"!`);
                        }
                        if (errorCount > 0) {
                            showProvvigioniAlert('danger', `${errorCount} provvigioni failed to update.`);
                        }

                        // Restore button state
                        changeStatusBtn.innerHTML = originalText;
                        changeStatusBtn.disabled = false;
                        updateChangeStatusButton();
                    }
                });
            });
        } catch (error) {
            console.error('Error in changeSospesoToInserito:', error);
            alert('Error: ' + error.message);
        }
    }

    // Function to update the change status button state
    function updateChangeStatusButton() {
        const changeStatusBtn = document.getElementById('changeStatusBtn');

        if (!changeStatusBtn) return;

        // Count all items with "Sospeso" status (not just checked ones)
        const allRows = document.querySelectorAll('tbody tr');
        let sospesoCount = 0;
        allRows.forEach(row => {
            const select = row.querySelector('.stato-select');
            if (select && select.value === 'Sospeso') {
                sospesoCount++;
            }
        });

        if (sospesoCount > 0) {
            changeStatusBtn.disabled = false;
            changeStatusBtn.innerHTML = `<i class="fas fa-exchange-alt mr-1"></i> All Sospeso → Inserito (${sospesoCount})`;
        } else {
            changeStatusBtn.disabled = true;
            changeStatusBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> All Sospeso → Inserito';
        }
    }

    // Initialize button state on page load
    updateChangeStatusButton();
});
</script>

@section('css')
<style>
    /* Pagination Styles */
    .pagination {
        margin: 0;
    }
    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
    }
    .page-link i {
        font-size: 0.8rem;
    }
    .page-item.active .page-link {
        z-index: 1;
    }
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: color 0.2s ease;
    }

    .sortable-header:hover {
        color: #007bff !important;
        text-decoration: none !important;
    }

    .sortable-header i {
        transition: transform 0.2s ease;
    }

    .sortable-header:hover i {
        transform: scale(1.1);
    }

    .table th a {
        display: block;
        width: 100%;
        height: 100%;
    }
</style>
@endsection
@endsection
