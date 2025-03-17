@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Messages</h2>
        <a href="{{ route('messages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Message
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Subject</th>
                            <th>From</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr class="{{ !$message->is_read ? 'table-active' : '' }}">
                                <td>
                                    <i class="bi {{ $message->is_read ? 'bi-envelope-open' : 'bi-envelope-fill' }} 
                                        {{ $message->is_read ? 'text-muted' : 'text-primary' }}"></i>
                                </td>
                                <td>
                                    <a href="{{ route('messages.show', $message) }}" class="text-decoration-none">
                                        {{ $message->subject }}
                                    </a>
                                </td>
                                <td>{{ $message->user->name }}</td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('messages.show', $message) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('messages.edit', $message) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No messages found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
