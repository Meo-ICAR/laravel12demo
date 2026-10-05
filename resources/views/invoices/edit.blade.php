@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Invoice</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $invoice->invoice_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fornitore">Fornitore</label>
                            <input type="text" class="form-control" id="fornitore" name="fornitore" value="{{ $invoice->fornitore }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date</label>
                            <input type="text" class="form-control" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" value="€ {{ number_format($invoice->total_amount, 2, ',', '.') }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delta">Delta</label>
                            <input type="number" step="0.01" class="form-control @error('delta') is-invalid @enderror" id="delta" name="delta" value="{{ old('delta', $invoice->delta) }}">
                            @error('delta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                   <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="competenza">Competenza </label>
                            <input type="number" step="0.01" class="form-control @error('competenza') is-invalid @enderror" id="competenza" name="competenza" value="{{ old('competenza', $invoice->competenza) }}">
                            @error('competenza')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_notenasarco">Is Notenasarco</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_notenasarco" name="is_notenasarco" value="1"
                                       {{ old('is_notenasarco', $invoice->is_notenasarco) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_notenasarco">Escludi da ENASARCO</label>
                            </div>
                            @error('isreconiled')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                @foreach($statusOptions as $option)
                                    <option value="{{ $option }}" {{ old('status', $invoice->status) == $option ? 'selected' : '' }}>
                                        {{ ucfirst($option) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="isreconiled">Is Reconciled</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isreconiled" name="isreconiled" value="1"
                                       {{ old('isreconiled', $invoice->isreconiled) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isreconiled">Mark as reconciled</label>
                            </div>
                            @error('isreconiled')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="paid_at">Paid At</label>
                            <input type="date" name="paid_at" id="paid_at" class="form-control @error('paid_at') is-invalid @enderror"
                                   value="{{ old('paid_at', $invoice->paid_at ? $invoice->paid_at->format('Y-m-d') : '') }}">
                            @error('paid_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sended_at">Sended At</label>
                            <input type="datetime-local" name="sended_at" id="sended_at" class="form-control @error('sended_at') is-invalid @enderror"
                                   value="{{ old('sended_at', $invoice->sended_at ? $invoice->sended_at->format('Y-m-d\TH:i') : '') }}">
                            @error('sended_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sended2_at">Sended2 At</label>
                            <input type="datetime-local" name="sended2_at" id="sended2_at" class="form-control @error('sended2_at') is-invalid @enderror"
                                   value="{{ old('sended2_at', $invoice->sended2_at ? $invoice->sended2_at->format('Y-m-d\TH:i') : '') }}">
                            @error('sended2_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <a href="{{ route('invoices.reconciliation') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
