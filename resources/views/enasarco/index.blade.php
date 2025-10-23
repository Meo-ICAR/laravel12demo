@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestione ENASARCO</h1>
        <a href="{{ route('enasarco.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            Nuovo Record
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anno</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minimo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Massimo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minimale</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Massimale</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($enasarco as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($item->enasarco) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->competenza }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($item->minimo, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($item->massimo, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($item->minimale, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($item->massimale, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('enasarco.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Modifica</a>
                            <form action="{{ route('enasarco.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Elimina</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Nessun record trovato
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $enasarco->links() }}
    </div>
</div>
@endsection
