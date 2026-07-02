@extends('layouts.nav')

@section('title', 'Chat')

@section('content')
    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="chat-shell">
        <aside class="chat-sidebar-panel">
            <div class="chat-profile">
                <div class="chat-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->course?->name ?? 'No course selected' }}</small>
                </div>
            </div>

            <div class="chat-group-card active">
                <div class="chat-avatar small">C</div>
                <div>
                    <strong>{{ $course?->name ?? 'Course group' }}</strong>
                    <small>{{ $members->count() }} members</small>
                </div>
            </div>

            <div class="chat-members">
                <p>Members</p>

                @forelse ($members as $member)
                    <div>
                        <span>{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                        <small>{{ $member->name }}</small>
                    </div>
                @empty
                    <small>No members found.</small>
                @endforelse
            </div>
        </aside>

        <div class="chat-window">
            @if ($course)
                <div class="chat-messages">
                    @forelse ($messages as $message)
                        @php
                            $isMine = $message->sender_id === auth()->id();
                            $wasEdited = $message->updated_at->gt($message->created_at->copy()->addSecond());
                        @endphp

                        <div @class(['chat-message-row', 'mine' => $isMine])>
                            @unless ($isMine)
                                <div class="chat-avatar tiny">{{ strtoupper(substr($message->sender->name, 0, 1)) }}</div>
                            @endunless

                            <div @class(['chat-bubble', 'mine' => $isMine])>
                                @if ($message->body)
                                    <p>{{ $message->body }}</p>
                                @endif

                                @if ($message->attachment_path)
                                    <a class="chat-attachment" href="{{ asset($message->attachment_path) }}" target="_blank">
                                        <span class="material-symbols-outlined">attach_file</span>
                                        {{ $message->attachment_name }}
                                    </a>
                                @endif

                                <small>
                                    {{ $message->sender->name }} · {{ $message->created_at->format('d M H:i') }}

                                    @if ($wasEdited)
                                        · edited at {{ $message->updated_at->format('H:i') }}
                                    @endif
                                </small>
                            </div>

                            @if ($isMine)
                                <details class="chat-message-menu">
                                    <summary>
                                        <span class="material-symbols-outlined">more_horiz</span>
                                    </summary>

                                    <div>
                                        <a href="#edit-message-{{ $message->id }}">Edit</a>

                                        <form method="POST" action="{{ route('chat.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <div class="chat-no-messages">
                            <span class="material-symbols-outlined">forum</span>
                            <strong>No messages yet</strong>
                            <p>Start the course group conversation.</p>
                        </div>
                    @endforelse
                </div>

                <form class="chat-composer" method="POST" action="{{ route('chat.store') }}" enctype="multipart/form-data">
                    @csrf

                    <input name="body" type="text" placeholder="Write something" autocomplete="off">

                    <label class="chat-plain-icon" for="chat-attachment">
                        <span class="material-symbols-outlined">attach_file</span>
                    </label>
                    <input id="chat-attachment" name="attachment" type="file" hidden>
                    <span class="chat-file-name" id="chat-file-name"></span>

                    <button type="submit">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            @else
                <div class="chat-no-messages">
                    <span class="material-symbols-outlined">forum</span>
                    <strong>No course group yet</strong>
                    <p>Select a course in your account before using course chat.</p>
                </div>
            @endif
        </div>
    </section>

    @foreach ($messages as $message)
        @if ($message->sender_id === auth()->id())
            <div id="edit-message-{{ $message->id }}" class="chat-edit-overlay">
                <form class="chat-edit-box" method="POST" action="{{ route('chat.update', $message) }}">
                    @csrf
                    @method('PATCH')

                    <div>
                        <strong>Edit message</strong>
                        <a href="#">Close</a>
                    </div>

                    <textarea name="body" rows="4" required>{{ $message->body }}</textarea>

                    <button type="submit">Save</button>
                </form>
            </div>
        @endif
    @endforeach

    <script>
        const chatAttachment = document.getElementById('chat-attachment');
        const chatFileName = document.getElementById('chat-file-name');

        if (chatAttachment && chatFileName) {
            chatAttachment.addEventListener('change', function () {
                chatFileName.textContent = this.files.length ? this.files[0].name : '';
            });
        }
    </script>
@endsection
