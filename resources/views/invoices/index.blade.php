@extends('layouts.admin')

@section('title', 'Invoices')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Invoices</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Invoices</li>
            </ol>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-12">
            <a href="{{ route('invoices.reconciliation') }}" class="btn btn-warning">
                <i class="fas fa-balance-scale mr-1"></i> Reconciliation Dashboard
            </a>
            <a href="{{ route('invoices.import') }}" class="btn btn-primary">
                <i class="fas fa-upload mr-1"></i> Import Invoices
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoices</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="#" class="xml-viewer-link" data-invoice-id="{{ $invoice->id }}" style="color: #007bff; text-decoration: underline; font-weight: 500;">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-right">€ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
                                    <td>
                                        @if($invoice->isreconiled)
                                            <span class="badge badge-success">Reconciled</span>
                                        @else
                                            <span class="badge badge-warning">Unreconciled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.reconciliation') }}" class="btn btn-sm btn-primary">Reconcile</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced XML Viewer Modal -->
<div class="modal fade" id="xmlViewerModal" tabindex="-1" role="dialog" aria-labelledby="xmlViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="xmlViewerModalLabel">
                    <i class="fas fa-file-code mr-2"></i>
                    Invoice XML Data
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Loading Spinner -->
                <div id="xmlLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading XML data...</p>
                </div>

                <!-- Content Container -->
                <div id="xmlContent" style="display: none;">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="xmlTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="fattura-tab" data-toggle="tab" href="#fattura-content" role="tab">
                                <i class="fas fa-receipt mr-1"></i>Fattura Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="structured-tab" data-toggle="tab" href="#structured-content" role="tab">
                                <i class="fas fa-table mr-1"></i>Structured Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="validation-tab" data-toggle="tab" href="#validation-content" role="tab">
                                <i class="fas fa-check-circle mr-1"></i>Validation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="xml-tree-tab" data-toggle="tab" href="#xml-tree-content" role="tab">
                                <i class="fas fa-sitemap mr-1"></i>XML Tree
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="raw-xml-tab" data-toggle="tab" href="#raw-xml-content" role="tab">
                                <i class="fas fa-code mr-1"></i>Raw XML
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="xmlTabContent">
                        <!-- Fattura Elettronica Data Tab -->
                        <div class="tab-pane fade show active" id="fattura-content" role="tabpanel">
                            <div id="fatturaData"></div>
                        </div>

                        <!-- Structured Data Tab -->
                        <div class="tab-pane fade" id="structured-content" role="tabpanel">
                            <div id="structuredData"></div>
                        </div>

                        <!-- Validation Tab -->
                        <div class="tab-pane fade" id="validation-content" role="tabpanel">
                            <div id="validationData"></div>
                        </div>

                        <!-- XML Tree Tab -->
                        <div class="tab-pane fade" id="xml-tree-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>XML Structure Tree</h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleAllNodes()">
                                    <i class="fas fa-expand-arrows-alt mr-1"></i>Toggle All
                                </button>
                            </div>
                            <div id="xmlTree" class="border rounded p-3 bg-light" style="max-height: 500px; overflow-y: auto;"></div>
                        </div>

                        <!-- Raw XML Tab -->
                        <div class="tab-pane fade" id="raw-xml-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Raw XML Content</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="copyXmlToClipboard()">
                                    <i class="fas fa-copy mr-1"></i>Copy XML
                                </button>
                            </div>
                            <pre id="rawXml" class="border rounded p-3 bg-dark text-light" style="max-height: 500px; overflow-y: auto; font-size: 12px;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Error</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .xml-tree-node {
        margin-left: 20px;
        border-left: 1px solid #dee2e6;
        padding-left: 10px;
    }

    .xml-tree-toggle {
        cursor: pointer;
        color: #007bff;
        margin-right: 5px;
    }

    .xml-tree-toggle:hover {
        color: #0056b3;
    }

    .xml-tree-content {
        display: none;
    }

    .xml-tree-content.expanded {
        display: block;
    }

    .xml-attribute {
        color: #28a745;
        font-weight: 500;
    }

    .xml-value {
        color: #dc3545;
        font-weight: 500;
    }

    .xml-tag {
        color: #007bff;
        font-weight: 600;
    }

    .fattura-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #007bff;
    }

    .fattura-section h6 {
        color: #007bff;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .fattura-field {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .fattura-field:last-child {
        border-bottom: none;
    }

    .fattura-label {
        font-weight: 500;
        color: #495057;
    }

    .fattura-value {
        color: #212529;
        text-align: right;
    }

    .validation-success {
        color: #28a745;
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        padding: 10px;
    }

    .validation-error {
        color: #721c24;
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 10px;
    }

    .validation-warning {
        color: #856404;
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        padding: 10px;
    }

    .line-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .line-item-header {
        background: #e9ecef;
        padding: 5px 10px;
        margin: -10px -10px 10px -10px;
        border-radius: 4px 4px 0 0;
        font-weight: 600;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.xml-viewer-link').click(function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('invoice-id');
        loadXmlData(invoiceId);
    });
});

function loadXmlData(invoiceId) {
    $('#xmlViewerModal').modal('show');
    $('#xmlLoading').show();
    $('#xmlContent').hide();

    $.ajax({
        url: `/invoices/${invoiceId}/xml-data`,
        method: 'GET',
        success: function(response) {
            $('#xmlLoading').hide();
            $('#xmlContent').show();

            if (response.success) {
                if (response.is_fattura_elettronica) {
                    displayFatturaElettronicaData(response.fattura_data);
                } else {
                    displayGenericXmlData(response);
                }

                // Always show raw XML
                $('#rawXml').text(response.formatted_xml || response.raw_xml);
                renderXmlTree(response.raw_xml);

            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            $('#xmlLoading').hide();
            showError('Failed to load XML data: ' + xhr.responseText);
        }
    });
}

function displayFatturaElettronicaData(fatturaData) {
    let html = '<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>This is a valid Fattura Elettronica document</div>';

    // Header Section
    html += '<div class="fattura-section">';
    html += '<h6><i class="fas fa-heading mr-2"></i>Document Header</h6>';

    if (fatturaData.header.dati_trasmissione) {
        html += '<div class="fattura-field"><span class="fattura-label">Transmitter ID:</span><span class="fattura-value">' + (fatturaData.header.dati_trasmissione.id_trasmittente || 'N/A') + '</span></div>';
        html += '<div class="fattura-field"><span class="fattura-label">Progressive Send:</span><span class="fattura-value">' + (fatturaData.header.dati_trasmissione.progressivo_invio || 'N/A') + '</span></div>';
        html += '<div class="fattura-field"><span class="fattura-label">Transmission Format:</span><span class="fattura-value">' + (fatturaData.header.dati_trasmissione.formato_trasmissione || 'N/A') + '</span></div>';
        html += '<div class="fattura-field"><span class="fattura-label">Recipient Code:</span><span class="fattura-value">' + (fatturaData.header.dati_trasmissione.codice_destinatario || 'N/A') + '</span></div>';
    }
    html += '</div>';

    // Supplier Section
    if (fatturaData.header.cedente_prestatore) {
        html += '<div class="fattura-section">';
        html += '<h6><i class="fas fa-building mr-2"></i>Supplier Information</h6>';

        const cedente = fatturaData.header.cedente_prestatore;
        if (cedente.dati_anagrafici) {
            const anagrafica = cedente.dati_anagrafici.anagrafica;
            html += '<div class="fattura-field"><span class="fattura-label">Name:</span><span class="fattura-value">' + (anagrafica.denominazione || anagrafica.nome + ' ' + anagrafica.cognome || 'N/A') + '</span></div>';
            html += '<div class="fattura-field"><span class="fattura-label">VAT ID:</span><span class="fattura-value">' + (cedente.dati_anagrafici.id_fiscale_iva.id_paese + cedente.dati_anagrafici.id_fiscale_iva.id_codice || 'N/A') + '</span></div>';
            html += '<div class="fattura-field"><span class="fattura-label">Fiscal Code:</span><span class="fattura-value">' + (cedente.dati_anagrafici.codice_fiscale || 'N/A') + '</span></div>';
        }

        if (cedente.sede) {
            html += '<div class="fattura-field"><span class="fattura-label">Address:</span><span class="fattura-value">' + (cedente.sede.indirizzo + ' ' + cedente.sede.numero_civico || 'N/A') + '</span></div>';
            html += '<div class="fattura-field"><span class="fattura-label">City:</span><span class="fattura-value">' + (cedente.sede.cap + ' ' + cedente.sede.comune + ' (' + cedente.sede.provincia + ')' || 'N/A') + '</span></div>';
        }
        html += '</div>';
    }

    // Customer Section
    if (fatturaData.header.cessionario_committente) {
        html += '<div class="fattura-section">';
        html += '<h6><i class="fas fa-user mr-2"></i>Customer Information</h6>';

        const cessionario = fatturaData.header.cessionario_committente;
        if (cessionario.dati_anagrafici) {
            const anagrafica = cessionario.dati_anagrafici.anagrafica;
            html += '<div class="fattura-field"><span class="fattura-label">Name:</span><span class="fattura-value">' + (anagrafica.denominazione || anagrafica.nome + ' ' + anagrafica.cognome || 'N/A') + '</span></div>';
            html += '<div class="fattura-field"><span class="fattura-label">VAT ID:</span><span class="fattura-value">' + (cessionario.dati_anagrafici.id_fiscale_iva.id_paese + cessionario.dati_anagrafici.id_fiscale_iva.id_codice || 'N/A') + '</span></div>';
        }
        html += '</div>';
    }

    // Body Sections
    if (fatturaData.body && fatturaData.body.length > 0) {
        fatturaData.body.forEach((body, index) => {
            // General Data
            if (body.dati_generali) {
                html += '<div class="fattura-section">';
                html += '<h6><i class="fas fa-file-invoice mr-2"></i>Document Details</h6>';
                html += '<div class="fattura-field"><span class="fattura-label">Document Type:</span><span class="fattura-value">' + (body.dati_generali.tipo_documento || 'N/A') + '</span></div>';
                html += '<div class="fattura-field"><span class="fattura-label">Document Number:</span><span class="fattura-value">' + (body.dati_generali.numero || 'N/A') + '</span></div>';
                html += '<div class="fattura-field"><span class="fattura-label">Date:</span><span class="fattura-value">' + (body.dati_generali.data || 'N/A') + '</span></div>';
                html += '<div class="fattura-field"><span class="fattura-label">Currency:</span><span class="fattura-value">' + (body.dati_generali.divisa || 'N/A') + '</span></div>';
                html += '</div>';
            }

            // Line Items
            if (body.dati_beni_servizi && body.dati_beni_servizi.linee) {
                html += '<div class="fattura-section">';
                html += '<h6><i class="fas fa-list mr-2"></i>Line Items</h6>';

                body.dati_beni_servizi.linee.forEach((linea, lineIndex) => {
                    html += '<div class="line-item">';
                    html += '<div class="line-item-header">Line ' + (linea.numero_linea || lineIndex + 1) + '</div>';
                    html += '<div class="fattura-field"><span class="fattura-label">Description:</span><span class="fattura-value">' + (linea.descrizione || 'N/A') + '</span></div>';
                    html += '<div class="fattura-field"><span class="fattura-label">Quantity:</span><span class="fattura-value">' + (linea.quantita || 'N/A') + '</span></div>';
                    html += '<div class="fattura-field"><span class="fattura-label">Unit Price:</span><span class="fattura-value">€ ' + (linea.prezzo_unitario ? parseFloat(linea.prezzo_unitario).toFixed(2) : 'N/A') + '</span></div>';
                    html += '<div class="fattura-field"><span class="fattura-label">Total Price:</span><span class="fattura-value">€ ' + (linea.prezzo_totale ? parseFloat(linea.prezzo_totale).toFixed(2) : 'N/A') + '</span></div>';
                    html += '<div class="fattura-field"><span class="fattura-label">VAT Rate:</span><span class="fattura-value">' + (linea.aliquota_iva ? linea.aliquota_iva + '%' : 'N/A') + '</span></div>';
                    html += '</div>';
                });
                html += '</div>';
            }

            // Totals
            if (body.dati_beni_servizi) {
                html += '<div class="fattura-section">';
                html += '<h6><i class="fas fa-calculator mr-2"></i>Totals</h6>';
                html += '<div class="fattura-field"><span class="fattura-label">Document Total:</span><span class="fattura-value">€ ' + (body.dati_beni_servizi.importo_totale_documento ? parseFloat(body.dati_beni_servizi.importo_totale_documento).toFixed(2) : 'N/A') + '</span></div>';
                html += '</div>';
            }
        });
    }

    $('#fatturaData').html(html);

    // Show validation info
    if (fatturaData.validation) {
        displayValidationInfo(fatturaData.validation);
    }
}

function displayGenericXmlData(response) {
    let html = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>This is a generic XML document (not Fattura Elettronica)</div>';

    // Structured Data
    if (response.structured_data) {
        html += '<div class="fattura-section">';
        html += '<h6><i class="fas fa-table mr-2"></i>Extracted Data</h6>';

        const data = response.structured_data;
        if (data.invoice_details) {
            Object.keys(data.invoice_details).forEach(key => {
                html += '<div class="fattura-field"><span class="fattura-label">' + key + ':</span><span class="fattura-value">' + (data.invoice_details[key] || 'N/A') + '</span></div>';
            });
        }
        html += '</div>';
    }

    $('#fatturaData').html(html);

    // Show validation info
    if (response.validation_result) {
        displayValidationInfo(response.validation_result);
    }
}

function displayValidationInfo(validation) {
    let html = '';

    if (validation.valid) {
        html += '<div class="validation-success">';
        html += '<i class="fas fa-check-circle mr-2"></i>Document is valid';
        if (validation.schema_used) {
            html += '<br><strong>Schema:</strong> ' + validation.schema_used;
        }
        html += '</div>';
    } else {
        html += '<div class="validation-error">';
        html += '<i class="fas fa-times-circle mr-2"></i>Document validation failed';
        html += '</div>';
    }

    if (validation.errors && validation.errors.length > 0) {
        html += '<div class="validation-error mt-3">';
        html += '<h6>Errors:</h6><ul>';
        validation.errors.forEach(error => {
            html += '<li>' + error + '</li>';
        });
        html += '</ul></div>';
    }

    if (validation.warnings && validation.warnings.length > 0) {
        html += '<div class="validation-warning mt-3">';
        html += '<h6>Warnings:</h6><ul>';
        validation.warnings.forEach(warning => {
            html += '<li>' + warning + '</li>';
        });
        html += '</ul></div>';
    }

    $('#validationData').html(html);
}

function renderXmlTree(xmlString) {
    try {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlString, "text/xml");
        const treeHtml = buildXmlTree(xmlDoc.documentElement, 0);
        $('#xmlTree').html(treeHtml);
    } catch (error) {
        $('#xmlTree').html('<div class="alert alert-danger">Error parsing XML tree: ' + error.message + '</div>');
    }
}

function buildXmlTree(element, level) {
    let html = '';
    const indent = '  '.repeat(level);

    // Element tag
    html += '<div class="xml-tree-node">';
    html += '<span class="xml-tree-toggle" onclick="toggleNode(this)">';
    html += '<i class="fas fa-chevron-right"></i>';
    html += '</span>';
    html += '<span class="xml-tag">&lt;' + element.tagName + '</span>';

    // Attributes
    if (element.attributes.length > 0) {
        for (let i = 0; i < element.attributes.length; i++) {
            const attr = element.attributes[i];
            html += ' <span class="xml-attribute">' + attr.name + '</span>=<span class="xml-value">"' + attr.value + '"</span>';
        }
    }

    html += '<span class="xml-tag">&gt;</span>';

    // Content
    const content = element.textContent.trim();
    if (content && element.children.length === 0) {
        html += '<span class="xml-value">' + content + '</span>';
    }

    html += '<span class="xml-tag">&lt;/' + element.tagName + '&gt;</span>';

    // Children
    if (element.children.length > 0) {
        html += '<div class="xml-tree-content">';
        for (let i = 0; i < element.children.length; i++) {
            html += buildXmlTree(element.children[i], level + 1);
        }
        html += '</div>';
    }

    html += '</div>';
    return html;
}

function toggleNode(element) {
    const content = $(element).siblings('.xml-tree-content');
    const icon = $(element).find('i');

    if (content.hasClass('expanded')) {
        content.removeClass('expanded');
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        content.addClass('expanded');
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

function toggleAllNodes() {
    const allNodes = $('.xml-tree-toggle');
    const allContent = $('.xml-tree-content');
    const allIcons = $('.xml-tree-toggle i');

    if (allContent.hasClass('expanded')) {
        allContent.removeClass('expanded');
        allIcons.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        allContent.addClass('expanded');
        allIcons.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

function copyXmlToClipboard() {
    const xmlText = $('#rawXml').text();
    navigator.clipboard.writeText(xmlText).then(function() {
        // Show success message
        const button = $('button[onclick="copyXmlToClipboard()"]');
        const originalText = button.html();
        button.html('<i class="fas fa-check mr-1"></i>Copied!');
        button.removeClass('btn-outline-primary').addClass('btn-success');

        setTimeout(function() {
            button.html(originalText);
            button.removeClass('btn-success').addClass('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy XML to clipboard');
    });
}

function showError(message) {
    $('#errorMessage').text(message);
    $('#errorModal').modal('show');
}
</script>
@endpush
