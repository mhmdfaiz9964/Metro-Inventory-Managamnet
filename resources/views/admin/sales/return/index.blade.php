@extends('layouts.app')

@section('content')
<div class="container-fluid my-4 px-4">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Sale Returns</h4>
            <a href="{{ route('admin.sales-returns.create') }}" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>New Return</a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.sales-returns.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search returns..." onkeypress="if(event.keyCode==13){this.form.submit();}">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Sale</th>
                            <th>Return Date</th>
                            <th>Reason</th>
                            <th>Items</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($saleReturns as $return)
                        <tr>
                            <td>{{ $return->id }}</td>
                            <td>#{{ $return->sale->id ?? '-' }} | {{ $return->sale->salesperson->name ?? '-' }}</td>
                            <td>{{ $return->return_date->format('d M Y') }}</td>
                            <td>{{ $return->reason }}</td>
                            <td>
                                @foreach($return->items as $item)
                                    {{ $item->product->name ?? '-' }} ({{ $item->quantity }})<br>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.sales-returns.edit', $return->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteReturn({{ $return->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="delete-form-{{ $return->id }}" action="{{ route('admin.sales-returns.destroy', $return->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No returns found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($saleReturns->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $saleReturns->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteReturn(id){
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result)=>{
        if(result.isConfirmed){
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>
@endsection
