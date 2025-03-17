@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Feeds</h2>
        <a href="{{ route('feeds.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Feed
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @forelse($feeds as $feed)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    @if($feed->image_url)
                        <img src="{{ $feed->image_url }}" class="card-img-top" alt="{{ $feed->title }}">
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">{{ $feed->title }}</h5>
                            <span class="badge bg-{{ $feed->is_published ? 'success' : 'warning' }}">
                                {{ $feed->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <p class="card-text text-muted small">
                            By {{ $feed->user->name }} • 
                            {{ $feed->created_at->format('M d, Y') }}
                        </p>
                        <p class="card-text">{{ Str::limit($feed->content, 150) }}</p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('feeds.show', $feed) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ route('feeds.edit', $feed) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @if($feed->is_published)
                                <form action="{{ route('feeds.unpublish', $feed) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-secondary w-100">
                                        <i class="bi bi-eye-slash"></i> Unpublish
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('feeds.publish', $feed) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Publish
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('feeds.destroy', $feed) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Are you sure you want to delete this feed?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No feeds found. <a href="{{ route('feeds.create') }}" class="alert-link">Create your first feed</a>.
                </div>
            </div>
        @endforelse
    </div>
    
    <div class="d-flex justify-content-end mt-3">
        {{ $feeds->links() }}
    </div>
</div>
@endsection
