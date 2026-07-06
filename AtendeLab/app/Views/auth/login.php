<?php
declare(strict_types=1);

require_once __DIR__ . '/../layouts/config-view.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Entrar | AtendeLab</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="/atendelab/public/assets/css/style.css">

</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">

    <div class="card border-0 shadow-sm login-card">

        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">

                <div class="brand-mark mx-auto mb-3">
                    AL
                </div>

                <h1 class="h3 mb-1">
                    AtendeLab
                </h1>

                <p class="text-secondary mb-0">
                    Controle de atendimentos acadêmicos
                </p>

            </div>

            <?php if (!empty($mensagem)): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars(
                        (string) $mensagem,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>

            <?php if (!empty($erroLogin)): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars(
                        (string) $erroLogin,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>

            <form
                method="post"
                action="<?= $baseUrl ?>?controller=auth&action=entrar">

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label">

                        E-mail

                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        required
                        autofocus>

                </div>

                <div class="mb-4">

                    <label
                        for="senha"
                        class="form-label">

                        Senha

                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="senha"
                        name="senha"
                        required>

                </div>

                <button
                    class="btn btn-success w-100"
                    type="submit">

                    Entrar

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>