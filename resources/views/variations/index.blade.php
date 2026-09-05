@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page" id="page-variations">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Variation Management</h3>
                <div class="header-actions" style="display: flex; gap: 10px;">
                    <input type="text" class="input input-sm" placeholder="Search variations..."/>
                    <button class="btn btn-primary btn-sm" onclick="openVarModal()">+ Add Variation</button>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Variation Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($variations as $variation)
                        <tr>
                            <td>{{ $variation->name }}</td>
                            <td>
                                <form action="{{ route('shop.variations.destroy', $variation->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">No variations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    function openVarModal() {
        document.getElementById('varModal').classList.remove('hidden');
    }
    function closeVarModal() {
        document.getElementById('varModal').classList.add('hidden');
    }
</script>
@endsection
