@extends('layouts.admin')

@section('content')
<div class="container-fluid">
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
                    <div class="col-md-2">
                        <label for="fonte">Fonte:</label>
                        <input type="text" name="fonte" id="fonte"
                               class="form-control" value="{{ request('fonte') }}"
                               placeholder="Search...">
                    </div>
                    <div class="col-md-2">
                        <label for="data_status_pratica">Data Status Pratica:</label>
                        <input type="text" name="data_status_pratica" id="data_status_pratica"
                               class="form-control" value="{{ request('data_status_pratica') }}"
                               placeholder="Search...">
                    </div>
                    <div class="col-md-2">
                        <label for="sended_at">Sended At Date:</label>
                        <input type="date" name="sended_at" id="sended_at"
                               class="form-control" value="{{ request('sended_at') }}">
                    </div>
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-info mr-2">Filter</button>
                        <button type="button" class="btn btn-secondary ml-2" onclick="clearFilters()">Clear</button>
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
    </div>

    <!-- Summary Card -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">
                        Summary
                        @if(request()->has('stato') || request()->has('denominazione_riferimento') || request()->has('istituto_finanziario') || request()->has('cognome') || request()->has('fonte') || request()->has('data_status_pratica') || request()->has('sended_at'))
                            <span class="badge badge-info ml-2">Filtered Results</span>
                        @endif
                    </h5>
                    <p class="card-text">
                        <strong>Total Records:</strong> {{ number_format($totalCount) }} |
                        <strong>Total Importo:</strong> € {{ number_format($totalImporto, 2, ',', '.') }}
                    </p>
                    @if(request()->has('stato') || request()->has('denominazione_riferimento') || request()->has('istituto_finanziario') || request()->has('cognome') || request()->has('fonte') || request()->has('data_status_pratica') || request()->has('sended_at'))
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-filter mr-1"></i>
                                Active filters:
                                @if(request('stato')) <span class="badge badge-secondary">Stato: {{ request('stato') }}</span> @endif
                                @if(request('denominazione_riferimento')) <span class="badge badge-secondary">Denominazione: {{ request('denominazione_riferimento') }}</span> @endif
                                @if(request('istituto_finanziario')) <span class="badge badge-secondary">Istituto: {{ request('istituto_finanziario') }}</span> @endif
                                @if(request('cognome')) <span class="badge badge-secondary">Cognome: {{ request('cognome') }}</span> @endif
                                @if(request('fonte')) <span class="badge badge-secondary">Fonte: {{ request('fonte') }}</span> @endif
                                @if(request('data_status_pratica')) <span class="badge badge-secondary">Data Status: {{ request('data_status_pratica') }}</span> @endif
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
                    @if($provvigioni->hasPages())
                        <p class="card-text">
                            <small class="text-muted">Page {{ $provvigioni->currentPage() }} of {{ $provvigioni->lastPage() }}</small>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Provvigioni List</h3>
                <div>
                    <a href="{{ route('provvigioni.import') }}" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-upload mr-1"></i> Import Provvigioni
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
                            <th>Denominazione Riferimento</th>
                            <th class="text-right">Importo</th>
                            <th>Stato</th>
                            <th>Cognome</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Istituto Finanziario</th>
                            <th>Fonte</th>
                            <th>Sended At</th>
                            <th>Invoice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($provvigioni as $item)
                            <tr>
                                <td>{{ Str::limit($item->denominazione_riferimento, 20) }}</td>
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
                                <td>{{ $item->istituto_finanziario }}</td>
                                <td>{{ Str::limit($item->fonte, 6) }}</td>
                                <td>{{ $item->sended_at ? \Carbon\Carbon::parse($item->sended_at)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ Str::limit($item->invoice_number, 6) ?: 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('provvigioni.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
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
});

// Function to clear all filters and navigate to base URL
function clearFilters() {
    // Clear all form fields
    document.getElementById('stato').value = '';
    document.getElementById('denominazione_riferimento').value = '';
    document.getElementById('istituto_finanziario').value = '';
    document.getElementById('cognome').value = '';
    document.getElementById('fonte').value = '';
    document.getElementById('data_status_pratica').value = '';
    document.getElementById('sended_at').value = '';

    // Navigate to base URL without any parameters
    window.location.href = '{{ route("provvigioni.index") }}';
}
</script>
@endsection
