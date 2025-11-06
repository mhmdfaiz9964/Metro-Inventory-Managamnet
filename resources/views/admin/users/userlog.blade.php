@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">User Logs</h4>
                </div>

                <div class="card-body">
                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.user-logs.index') }}" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control" placeholder="Search by user name..." 
                                   onkeypress="if(event.keyCode==13){this.form.submit();}">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="{{ route('admin.user-logs.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                        </div>
                    </form>

                    {{-- Logs Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $index => $log)
                                <tr>
                                    <td>{{ $logs->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $log->user->name ?? 'Deleted User' }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->event == 'login' ? 'success' : 'danger' }}">
                                            @if($log->event == 'login')
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                            @else
                                                <i class="bi bi-box-arrow-left me-1"></i> Logout
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $log->ip_address ?? '-' }}</td>
                                    <td>{{ Str::limit($log->user_agent, 50) }}</td>
                                    <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($logs->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $logs->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
