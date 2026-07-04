@extends('layouts.nav')

@section('title', 'Chat')

@section('content')
    @php
        $user = auth()->user();
        $canManageGroups = $user->isTeacher() || $user->isAdmin();
        $canManageSelectedGroup = $selectedGroup && ($user->isAdmin() || $selectedGroup->teacher_id === $user->id);
    @endphp

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
                    <small>{{ $user->isAdmin() ? 'Conta de administrador' : ($user->course?->name ?? 'Nenhum curso selecionado') }}</small>
                </div>
            </div>

            <div class="chat-groups-area">
                @if ($user->isAdmin())
                    <div class="chat-group-filter">
                        <input id="chat-group-search" type="text" placeholder="Pesquisar grupos" autocomplete="off">
                    </div>
                @endif

                @if ($user->isAdmin() && $courses->isNotEmpty())
                    <div class="chat-group-list">
                        <p>Grupos de curso</p>

                        @foreach ($courses as $chatCourse)
                            <a
                                href="{{ route('chat', ['course' => $chatCourse->id]) }}"
                                data-chat-link
                                data-group-item
                                data-group-name="{{ strtolower($chatCourse->name.' course group') }}"
                                @class(['chat-group-card', 'active' => ! $selectedGroup && $course?->id === $chatCourse->id])
                            >
                                <div class="chat-avatar small">C</div>
                                <div>
                                    <strong>{{ $chatCourse->name }}</strong>
                                    <small>Grupo de curso</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif ($course)
                    <div class="chat-group-list">
                        <p>Grupos de curso</p>

                        <a href="{{ route('chat') }}" data-chat-link @class(['chat-group-card', 'active' => ! $selectedGroup])>
                            <div class="chat-avatar small">C</div>
                            <div>
                                <strong>{{ $course->name }}</strong>
                                <small>Grupo de curso</small>
                            </div>
                        </a>
                    </div>
                @endif

                @if ($groups->isNotEmpty())
                    <div class="chat-group-list">
                        <p>Grupos de professores</p>

                        @foreach ($groups as $group)
                            @php
                                $canManageThisGroup = $user->isAdmin() || $group->teacher_id === $user->id;
                            @endphp

                            <div
                                class="chat-group-row"
                                data-group-item
                                data-group-name="{{ strtolower($group->name.' '.$group->teacher->name.' teacher group') }}"
                            >
                                <a href="{{ route('chat', ['group' => $group->id]) }}" data-chat-link @class(['chat-group-card', 'active' => $selectedGroup?->id === $group->id])>
                                    <div class="chat-avatar small">G</div>
                                    <div>
                                        <strong>{{ $group->name }}</strong>
                                        <small>{{ $group->teacher->name }}</small>
                                    </div>
                                </a>

                                @if ($canManageThisGroup)
                                    <details class="chat-group-menu">
                                        <summary>
                                            <span class="material-symbols-outlined">more_horiz</span>
                                        </summary>

                                        <div>
                                            <a href="#edit-chat-group-{{ $group->id }}">Editar</a>

                                            <form method="POST" action="{{ route('chat.groups.destroy', $group) }}" onsubmit="return confirm(@json('Eliminar este grupo? Isto também elimina as mensagens.'));">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit">Eliminar</button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($canManageGroups)
                    <a class="chat-create-group-link" href="#new-chat-group">Criar grupo</a>
                @endif
            </div>

            <div class="chat-members">
                <div class="chat-members-title">
                    <p>Membros</p>

                    @if ($canManageSelectedGroup)
                        <a href="#add-student-popover">Adicionar aluno</a>
                    @endif

                    @if ($canManageSelectedGroup)
                        <div id="add-student-popover" class="chat-add-student-popover">
                            <form class="chat-add-student-form" method="POST" action="{{ route('chat.groups.members.store', $selectedGroup) }}">
                                @csrf

                                <div class="chat-add-student-top">
                                    <strong>Adicionar aluno</strong>
                                    <a href="#">Fechar</a>
                                </div>

                                <div class="chat-add-student-row">
                                    <input id="student-search" type="text" placeholder="Pesquisar aluno" autocomplete="off">
                                    <button type="submit">Adicionar</button>
                                </div>

                                <input id="student-email" name="email" type="hidden" required>
                                <div class="student-search-results" id="student-search-results"></div>
                            </form>
                        </div>
                    @endif
                </div>

                <input class="chat-member-search" id="chat-member-search" type="text" placeholder="Pesquisar membros" autocomplete="off">

                <div class="chat-member-list">
                    @forelse ($members as $member)
                        <div class="chat-member-row" data-member-name="{{ strtolower($member->name.' '.$member->email.' '.$member->role) }}">
                            <span>{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                            <small>
                                {{ $member->name }}

                                @if ($member->isAdmin())
                                    · Administrador
                                @endif
                            </small>
                        </div>
                    @empty
                        <small>Nenhum membro encontrado.</small>
                    @endforelse
                </div>
            </div>
        </aside>

        <div class="chat-window">
            @if ($course || $selectedGroup)
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
                                @if ($message->sharedNote)
                                    <button class="chat-shared-note-card" type="button" data-open-shared-note="shared-note-{{ $message->id }}">
                                        <span class="material-symbols-outlined">description</span>
                                        <div>
                                            <strong>{{ $message->sharedNote->title }}</strong>
                                            <small>Clica para pré-visualizar o apontamento</small>
                                        </div>
                                    </button>
                                @elseif ($message->body)
                                    <p>{{ $message->body }}</p>
                                @endif

                                @if ($message->attachment_path)
                                    <a class="chat-attachment" href="{{ asset($message->attachment_path) }}" target="_blank">
                                        <span class="material-symbols-outlined">attach_file</span>
                                        {{ $message->attachment_name }}
                                    </a>
                                @endif

                                <small>
                                    {{ $message->sender->name }} · {{ $message->created_at->format('d/m H:i') }}

                                    @if ($wasEdited)
                                        · editado às {{ $message->updated_at->format('H:i') }}
                                    @endif
                                </small>
                            </div>

                            @if ($isMine)
                                <details class="chat-message-menu">
                                    <summary>
                                        <span class="material-symbols-outlined">more_horiz</span>
                                    </summary>

                                    <div>
                                        <a href="#edit-message-{{ $message->id }}">Editar</a>

                                        <form method="POST" action="{{ route('chat.destroy', $message) }}" onsubmit="return confirm(@json('Eliminar esta mensagem?'));">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <div class="chat-no-messages">
                            <span class="material-symbols-outlined">forum</span>
                            <strong>Ainda não há mensagens</strong>
                            <p>Começa a conversa do grupo de curso.</p>
                        </div>
                    @endforelse
                </div>

                <form class="chat-composer" method="POST" action="{{ route('chat.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if ($selectedGroup)
                        <input name="chat_group_id" type="hidden" value="{{ $selectedGroup->id }}">
                    @elseif ($user->isAdmin() && $course)
                        <input name="course_id" type="hidden" value="{{ $course->id }}">
                    @endif

                    <input name="body" type="text" placeholder="Escreve algo" autocomplete="off">

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
                    <strong>Ainda não há grupo de chat</strong>
                    <p>Seleciona um grupo de curso ou grupo de professor antes de conversar.</p>
                </div>
            @endif
        </div>
    </section>

    <div id="chat-overlays">
        @foreach ($messages as $message)
            @if ($message->sharedNote)
                <div id="shared-note-{{ $message->id }}" class="chat-note-preview-layer">
                    <div class="chat-note-preview-box">
                        <div class="chat-note-preview-top">
                            <strong>Pré-visualização do apontamento partilhado</strong>
                            <button type="button" data-close-shared-note>Fechar</button>
                        </div>

                        <div class="chat-note-preview-title">
                            <h2>{{ $message->sharedNote->title }}</h2>

                            <div>
                                @if ($message->sharedNote->tag)
                                    <span @class(['note-tag', 'note-tag-'.$message->sharedNote->tag])>
                                        {{ ucfirst($message->sharedNote->tag) }}
                                    </span>
                                @endif

                                @if ($message->sharedNote->subject)
                                    <span class="note-subject-tag">{{ $message->sharedNote->subject->name }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($message->sharedNote->description)
                            <p class="chat-note-preview-description">{{ $message->sharedNote->description }}</p>
                        @endif

                        <div class="chat-note-preview-body">{{ trim($message->sharedNote->body ?: 'Este apontamento ainda não tem conteúdo.') }}</div>

                        <div class="chat-note-preview-actions">
                            @if ($message->sharedNote->user_id === auth()->id())
                                <a class="chat-note-preview-button" href="{{ route('notes') }}#note-{{ $message->sharedNote->id }}">
                                    Abrir o meu apontamento
                                </a>
                            @elseif ($user->isAdmin())
                                <p class="chat-note-preview-admin-text">Os administradores podem pré-visualizar apontamentos partilhados, mas só alunos e professores guardam apontamentos.</p>
                            @else
                                <form method="POST" action="{{ route('notes.copy', $message->sharedNote) }}">
                                    @csrf

                                    <button class="chat-note-preview-button" type="submit">
                                        Guardar nos meus apontamentos
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($message->sender_id === auth()->id())
                <div id="edit-message-{{ $message->id }}" class="chat-edit-overlay">
                    <form class="chat-edit-box" method="POST" action="{{ route('chat.update', $message) }}">
                        @csrf
                        @method('PATCH')

                        <div>
                            <strong>Editar mensagem</strong>
                            <a href="#">Fechar</a>
                        </div>

                        <textarea name="body" rows="4" required>{{ $message->body }}</textarea>

                        <button type="submit">Guardar</button>
                    </form>
                </div>
            @endif
        @endforeach

        @if ($canManageGroups)
            <div id="new-chat-group" class="chat-edit-overlay">
                <form class="chat-edit-box" method="POST" action="{{ route('chat.groups.store') }}">
                    @csrf

                    <div>
                        <strong>Criar grupo</strong>
                        <a href="#">Fechar</a>
                    </div>

                    <input name="name" type="text" placeholder="Nome do grupo" required>

                    <button type="submit">Criar</button>
                </form>
            </div>
        @endif

        @foreach ($groups as $group)
            @if ($user->isAdmin() || $group->teacher_id === $user->id)
                <div id="edit-chat-group-{{ $group->id }}" class="chat-edit-overlay">
                    <form class="chat-edit-box" method="POST" action="{{ route('chat.groups.update', $group) }}">
                        @csrf
                        @method('PATCH')

                        <div>
                            <strong>Editar grupo</strong>
                            <a href="#">Fechar</a>
                        </div>

                        <input name="name" type="text" value="{{ $group->name }}" required>

                        <button type="submit">Guardar</button>
                    </form>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        const students = @json($students->map(fn ($student) => [
            'name' => $student->name,
            'email' => $student->email,
        ])->values());

        scrollCurrentMessagesToBottom();

        document.addEventListener('click', function (event) {
            const chatLink = event.target.closest('[data-chat-link]');
            const studentButton = event.target.closest('[data-student-email]');
            const sharedNoteButton = event.target.closest('[data-open-shared-note]');
            const closeSharedNoteButton = event.target.closest('[data-close-shared-note]');

            if (event.target.classList.contains('chat-note-preview-layer')) {
                event.target.classList.remove('open');
            }

            if (sharedNoteButton) {
                const preview = document.getElementById(sharedNoteButton.dataset.openSharedNote);

                if (preview) {
                    preview.classList.add('open');
                }
            }

            if (closeSharedNoteButton) {
                closeSharedNoteButton.closest('.chat-note-preview-layer').classList.remove('open');
            }

            if (chatLink) {
                event.preventDefault();
                openChatWithoutReload(chatLink.href);
            }

            if (studentButton) {
                const studentSearch = document.getElementById('student-search');
                const studentEmail = document.getElementById('student-email');
                const studentResults = document.getElementById('student-search-results');

                studentSearch.value = studentButton.dataset.studentName;
                studentEmail.value = studentButton.dataset.studentEmail;
                studentResults.innerHTML = '';
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.id === 'chat-attachment') {
                const chatFileName = document.getElementById('chat-file-name');
                chatFileName.textContent = event.target.files.length ? event.target.files[0].name : '';
            }
        });

        document.addEventListener('input', function (event) {
            if (event.target.id === 'chat-group-search') {
                filterGroups(event.target.value);
            }

            if (event.target.id === 'chat-member-search') {
                filterMembers(event.target.value);
            }

            if (event.target.id === 'student-search') {
                searchStudents(event.target.value);
            }
        });

        window.addEventListener('popstate', function () {
            openChatWithoutReload(window.location.href, false);
        });

        function filterGroups(value) {
            const search = value.toLowerCase().trim();

            document.querySelectorAll('[data-group-item]').forEach(function (item) {
                const groupName = item.dataset.groupName || '';
                item.hidden = search && ! groupName.includes(search);
            });
        }

        function filterMembers(value) {
            const search = value.toLowerCase().trim();

            document.querySelectorAll('[data-member-name]').forEach(function (member) {
                const memberName = member.dataset.memberName || '';
                member.hidden = search && ! memberName.includes(search);
            });
        }

        function searchStudents(value) {
            const studentEmail = document.getElementById('student-email');
            const studentResults = document.getElementById('student-search-results');
            const search = value.toLowerCase().trim();

            studentEmail.value = '';
            studentResults.innerHTML = '';

            if (!search) {
                return;
            }

            students
                .filter(function (student) {
                    return student.name.toLowerCase().includes(search)
                        || student.email.toLowerCase().includes(search);
                })
                .slice(0, 6)
                .forEach(function (student) {
                    const button = document.createElement('button');
                    const name = document.createElement('strong');
                    const email = document.createElement('small');

                    button.type = 'button';
                    button.dataset.studentName = student.name;
                    button.dataset.studentEmail = student.email;
                    name.textContent = student.name;
                    email.textContent = student.email;
                    button.appendChild(name);
                    button.appendChild(email);
                    studentResults.appendChild(button);
                });
        }

        function openChatWithoutReload(url, saveHistory = true) {
            const groupArea = document.querySelector('.chat-groups-area');
            const oldGroupScroll = groupArea ? groupArea.scrollTop : 0;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {
                    const page = new DOMParser().parseFromString(html, 'text/html');
                    const newChat = page.querySelector('.chat-shell');
                    const newOverlays = page.querySelector('#chat-overlays');

                    document.querySelector('.chat-shell').replaceWith(newChat);
                    document.querySelector('#chat-overlays').replaceWith(newOverlays);

                    const newGroupArea = document.querySelector('.chat-groups-area');
                    const messages = document.querySelector('.chat-messages');

                    if (newGroupArea) {
                        newGroupArea.scrollTop = oldGroupScroll;
                    }

                    if (messages) {
                        scrollCurrentMessagesToBottom();
                    }

                    if (saveHistory) {
                        history.pushState({}, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
        }

        function scrollCurrentMessagesToBottom() {
            const messages = document.querySelector('.chat-messages');

            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }
        }
    </script>
@endsection
