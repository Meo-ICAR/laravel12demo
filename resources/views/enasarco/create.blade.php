@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nuovo Record ENASARCO</h1>
        <a href="{{ route('enasarco.index') }}" class="text-gray-600 hover:text-gray-800">
            Torna all'elenco
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('enasarco.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="enasarco" class="block text-sm font-medium text-gray-700">Tipo ENASARCO *</label>
                    <select name="enasarco" id="enasarco" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Seleziona tipo</option>
                        @foreach($enasarcoTypes as $type)
                            <option value="{{ $type }}" {{ old('enasarco') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    @error('enasarco')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="competenza" class="block text-sm font-medium text-gray-700">Anno di competenza *</label>
                    <input type="number" name="competenza" id="competenza" min="2000" max="{{ $currentYear + 1 }}" 
                           value="{{ old('competenza', $currentYear) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('competenza')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="minimo" class="block text-sm font-medium text-gray-700">Importo minimo (€) *</label>
                    <input type="number" name="minimo" id="minimo" step="0.01" min="0" value="{{ old('minimo') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('minimo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="massimo" class="block text-sm font-medium text-gray-700">Importo massimo (€) *</label>
                    <input type="number" name="massimo" id="massimo" step="0.01" min="0" value="{{ old('massimo') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('massimo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="minimale" class="block text-sm font-medium text-gray-700">Importo minimale (€) *</label>
                    <input type="number" name="minimale" id="minimale" step="0.01" min="0" value="{{ old('minimale') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('minimale')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="massimale" class="block text-sm font-medium text-gray-700">Importo massimale (€) *</label>
                    <input type="number" name="massimale" id="massimale" step="0.01" min="0" value="{{ old('massimale') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('massimale')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('enasarco.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md">
                    Annulla
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                    Salva
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
