@extends('layouts.master')

@section('title', 'الإشعارات')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>الإشعارات</h4>

        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-outline-primary btn-sm">
                تحديد الكل كمقروء
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">

            @forelse($notifications as $notification)
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                    <div>
                        <div class="fw-bold {{ $notification->is_read ? 'text-muted' : '' }}">
                            {{ $notification->title }}
                        </div>

                        <div class="small {{ $notification->is_read ? 'text-muted' : '' }}">
                            {{ $notification->message }}
                        </div>

                        <div class="small text-muted">
                            {{ $notification->created_at?->diffForHumans() }}
                        </div>
                    </div>

                    <div>
                        @if(!$notification->is_read)
                            <span class="badge bg-danger">جديد</span>
                        @endif

                        <a href="{{ route('notifications.open', $notification) }}" class="btn btn-sm btn-outline-primary">
                            فتح
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted mb-0">
                    لا توجد إشعارات.
                </p>
            @endforelse

            <div class="mt-3">
                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection