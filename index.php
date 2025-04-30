<?php
session_start();

$erreurs = isset($_COOKIE['form_errors']) ? json_decode($_COOKIE['form_errors'], true) : [];
$valeurs = isset($_COOKIE['form_values']) ? json_decode($_COOKIE['form_values'], true) : [];
$valeurs_defaut = [
    "nom_complet" => "",
    "telephone" => "",
    "email" => "",
    "date_naissance" => "",
    "genre" => "",
    "langages" => [],
    "biographie" => "",
    "accord" => ""
];

// Préremplir avec les valeurs du cookie de succès s’il n’y a pas d’erreurs
if (empty($erreurs) && isset($_COOKIE['form_success_values'])) {
    $valeurs = json_decode($_COOKIE['form_success_values'], true);
}

// Supprimer les cookies de session après usage
setcookie("form_errors", "", time() - 3600, "/");
setcookie("form_values", "", time() - 3600, "/");

function get_valeur($champ, $defaut = "") {
    global $valeurs, $valeurs_defaut;
    return htmlspecialchars($valeurs[$champ] ?? $valeurs_defaut[$champ] ?? $defaut);
}

function champ_en_erreur($champ) {
    global $erreurs;
    return isset($erreurs[$champ]) ? 'error-field' : '';
}

function afficher_erreur($champ) {
    global $erreurs;
    return isset($erreurs[$champ]) ? "<div class='error-msg'>{$erreurs[$champ]}</div>" : "";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-field { border: 2px solid red !important; }
        .error-msg { color: red; font-size: 0.9em; margin-bottom: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Formulaire d'inscription</h1>
    <form action="process.php" method="post">
        <div class="form-group">
            <?= afficher_erreur("nom_complet") ?>
            <label for="nom_complet">Nom complet :</label>
            <input type="text" id="nom_complet" name="nom_complet" class="<?= champ_en_erreur("nom_complet") ?>" value="<?= get_valeur("nom_complet") ?>" required>
        </div>

        <div class="form-group">
            <?= afficher_erreur("telephone") ?>
            <label for="telephone">Téléphone :</label>
            <input type="tel" id="telephone" name="telephone" class="<?= champ_en_erreur("telephone") ?>" value="<?= get_valeur("telephone") ?>" required>
        </div>

        <div class="form-group">
            <?= afficher_erreur("email") ?>
            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" class="<?= champ_en_erreur("email") ?>" value="<?= get_valeur("email") ?>" required>
        </div>

        <div class="form-group">
            <?= afficher_erreur("date_naissance") ?>
            <label for="date_naissance">Date de naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" class="<?= champ_en_erreur("date_naissance") ?>" value="<?= get_valeur("date_naissance") ?>" required>
        </div>

        <div class="form-group">
            <?= afficher_erreur("genre") ?>
            <label>Genre :</label>
            <input type="radio" id="masculin" name="genre" value="masculin" <?= get_valeur("genre") == "masculin" ? "checked" : "" ?>> <label for="masculin">Masculin</label>
            <input type="radio" id="feminin" name="genre" value="feminin" <?= get_valeur("genre") == "feminin" ? "checked" : "" ?>> <label for="feminin">Féminin</label>
        </div>

        <div class="form-group">
            <?= afficher_erreur("langages") ?>
            <label>Langages de programmation préférés :</label>
            <select id="langages" name="langages[]" multiple required class="<?= champ_en_erreur("langages") ?>">
                <?php
                $options = ["Pascal", "C", "Haskel", "Clojure", "Prolog", "Scala", "Go"];
                $user_choices = $valeurs["langages"] ?? [];
                foreach ($options as $opt) {
                    $selected = in_array($opt, $user_choices) ? "selected" : "";
                    echo "<option value=\"$opt\" $selected>$opt</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <?= afficher_erreur("biographie") ?>
            <label for="biographie">Biographie :</label>
            <textarea id="biographie" name="biographie" rows="4" class="<?= champ_en_erreur("biographie") ?>" required><?= get_valeur("biographie") ?></textarea>
        </div>

        <div class="form-group">
            <?= afficher_erreur("accord") ?>
            <input type="checkbox" id="accord" name="accord" <?= get_valeur("accord") ? "checked" : "" ?> required>
            <label for="accord">J'ai pris connaissance du contrat</label>
        </div>

        <button type="submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
