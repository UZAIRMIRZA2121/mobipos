@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page" id="page-addons">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Add-ons Management</h3>
                <div class="header-actions" style="display: flex; gap: 10px;">
                    <input type="text" class="input input-sm" placeholder="Search add-ons..."/>
                    <button class="btn btn-primary btn-sm" onclick="openAddonModal()">+ Add Add-on</button>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Add-on Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addons as $addon)
                        <tr>
                            <td>{{ $addon->name }}</td>
                            <td>
                                <form action="{{ route('shop.addons.destroy', $addon->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">No add-ons found</td>
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
    function openAddonModal() {
        document.getElementById('addonModal').classList.remove('hidden');
    }
    function closeAddonModal() {
        document.getElementById('addonModal').classList.add('hidden');
    }
</script>
@endsection
