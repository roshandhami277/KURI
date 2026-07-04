<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&display=swap" rel="stylesheet">
</head>
<body>
    {{-- Top navigation --}}
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Kuri home">
            <span>Kuri</span>
        </a>

        <div class="header-actions">
            @auth
                <a href="{{ route('dashboard') }}">Painel</a>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}">Registar</a>
            @endauth
        </div>
    </header>

    <main class="welcome-page">
        <section class="welcome-hero">
            <div class="welcome-copy">
                <p class="eyebrow">Um espaço escolar</p>
                <h1>Mantém a vida escolar num lugar tranquilo.</h1>
                <p class="introduction">
                    O Kuri ajuda os alunos a organizar tarefas, notas, apontamentos, eventos do calendário, notícias e chats de curso sem tornar a app pesada.
                </p>

                <div class="welcome-actions">
                    @auth
                        <a class="primary-action" href="{{ route('dashboard') }}">Abrir painel</a>
                    @else
                        <a class="primary-action" href="{{ route('register') }}">Criar conta</a>
                        <a class="secondary-action" href="{{ route('login') }}">Entrar</a>
                    @endauth
                </div>

                <div class="welcome-pills" aria-label="Destaques do Kuri">
                    <span>Alunos</span>
                    <span>Professores</span>
                    <span>Grupos de curso</span>
                    <span>Apontamentos privados</span>
                </div>
            </div>

            <div class="welcome-preview" aria-label="Pré-visualização do Kuri">
                <div class="preview-top">
                    <span>K</span>
                    <div>
                        <strong>Hoje no Kuri</strong>
                        <small>Painel simples do aluno</small>
                    </div>
                </div>

                <div class="preview-list">
                    <div>
                        <span>✓</span>
                        <p>Acabar apontamentos de Biologia</p>
                    </div>
                    <div>
                        <span>↗</span>
                        <p>Adicionar nota de matemática</p>
                    </div>
                    <div>
                        <span>✎</span>
                        <p>Preparar apontamento de estudo</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="welcome-features" aria-label="Funcionalidades principais do Kuri">
            <article>
                <span>01</span>
                <h2>Planear</h2>
                <p>Usa tarefas diárias e eventos do calendário para te lembrares de trabalhos, testes e exames.</p>
            </article>

            <article>
                <span>02</span>
                <h2>Estudar</h2>
                <p>Guarda apontamentos por disciplina, adiciona etiquetas e partilha apontamentos úteis com os teus grupos.</p>
            </article>

            <article>
                <span>03</span>
                <h2>Progresso</h2>
                <p>Adiciona notas e vê o teu progresso por disciplina num gráfico simples.</p>
            </article>
        </section>

        <section class="welcome-demo">
            <div>
                <p class="eyebrow">Demonstração da app</p>
                <h2>Mostra como o Kuri funciona.</h2>
                <p>
                    Quando o projeto estiver pronto, esta secção pode ter um vídeo curto a mostrar o painel, notas, apontamentos, calendário, chat e notícias.
                </p>
            </div>

            {{-- Later you can replace this placeholder with a real <video> tag. --}}
            <div class="demo-placeholder">
                <span>▶</span>
                <strong>Vídeo de demonstração em breve</strong>
                <small>Lugar ideal para a demonstração da apresentação final.</small>
            </div>
        </section>
    </main>
</body>
</html>
