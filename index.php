<?php
// 1. Lire les cookies si présents
$form_errors = isset($_COOKIE['form_errors']) ? json_decode($_COOKIE['form_errors'], true) : [];
$form_values = isset($_COOKIE['form_values']) ? json_decode($_COOKIE['form_values'], true) : [];

if (empty($form_values) && isset($_COOKIE['form_success_values'])) {
    $form_values = json_decode($_COOKIE['form_success_values'], true);
}

// 2. Supprimer les cookies de session (erreurs + valeurs)
setcookie('form_errors', '', time() - 3600, '/');
setcookie('form_values', '', time() - 3600, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-field {
            border: 2px solid red !important;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Formulaire d'inscription</h1>

    <?php if (!empty($form_errors)): ?>
        <div class="error">
            <b>Veuillez corriger les erreurs suivantes :</b>
            <ul>
                <?php foreach ($form_errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="success">Données enregistrées avec succès !</div>
    <?php endif; ?>

    <form action="process.php" method="post">
        <div class="form-group">
            <label for="nom_complet">Nom complet :</label>
            <?php if (isset($form_errors['nom_complet'])): ?>
                <div class="error-message"><?= $form_errors['nom_complet'] ?></div>
            <?php endif; ?>
            <input type="text" id="nom_complet" name="nom_complet"
                value="<?= htmlspecialchars($form_values['nom_complet'] ?? '') ?>"
                class="<?= isset($form_errors['nom_complet']) ? 'error-field' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone :</label>
            <?php if (isset($form_errors['telephone'])): ?>
                <div class="error-message"><?= $form_errors['telephone'] ?></div>
            <?php endif; ?>
            <input type="tel" id="telephone" name="telephone"
                value="<?= htmlspecialchars($form_values['telephone'] ?? '') ?>"
                class="<?= isset($form_errors['telephone']) ? 'error-field' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail :</label>
            <?php if (isset($form_errors['email'])): ?>
                <div class="error-message"><?= $form_errors['email'] ?></div>
            <?php endif; ?>
            <input type="email" id="email" name="email"
                value="<?= htmlspecialchars($form_values['email'] ?? '') ?>"
                class="<?= isset($form_errors['email']) ? 'error-field' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="date_naissance">Date de naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance"
                value="<?= htmlspecialchars($form_values['date_naissance'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Genre :</label>
            <?php if (isset($form_errors['genre'])): ?>
                <div class="error-message"><?= $form_errors['genre'] ?></div>
            <?php endif; ?>
            <input type="radio" id="masculin" name="genre" value="masculin"
                <?= (isset($form_values['genre']) && $form_values['genre'] === 'masculin') ? 'checked' : '' ?> required>
            <label for="masculin">Masculin</label>
            <input type="radio" id="feminin" name="genre" value="feminin"
                <?= (isset($form_values['genre']) && $form_values['genre'] === 'feminin') ? 'checked' : '' ?> required>
            <label for="feminin">Féminin</label>
        </div>

        <div class="form-group">
            <label>Langages de programmation préférés :</label>
            <?php if (isset($form_errors['langages'])): ?>
                <div class="error-message"><?= $form_errors['langages'] ?></div>
            <?php endif; ?>
            <select id="langages" name="langages[]" multiple required
                class="<?= isset($form_errors['langages']) ? 'error-field' : '' ?>">
                <?php
                $options = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskel", "Clojure", "Prolog", "Scala", "Go"];
                foreach ($options as $lang) {
                    $selected = (isset($form_values['langages']) && in_array($lang, $form_values['langages'])) ? 'selected' : '';
                    echo "<option value=\"$lang\" $selected>$lang</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="biographie">Biographie :</label>
            <?php if (isset($form_errors['biographie'])): ?>
                <div class="error-message"><?= $form_errors['biographie'] ?></div>
            <?php endif; ?>
            <textarea id="biographie" name="biographie" rows="4"
                class="<?= isset($form_errors['biographie']) ? 'error-field' : '' ?>" required><?= htmlspecialchars($form_values['biographie'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <input type="checkbox" id="accord" name="accord" required
                <?= isset($form_values['accord']) ? 'checked' : '' ?>>
            <label for="accord">J'ai pris connaissance du contrat</label>
            <?php if (isset($form_errors['accord'])): ?>
                <div class="error-message"><?= $form_errors['accord'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Enregistrer</button>
    </form>
</div>

</body>
</html>
