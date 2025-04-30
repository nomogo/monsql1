<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Configuration de la base de données
$host = "localhost";
$username = "u68658";
$password = "7975806";
$database = "u68658";

// 2. Fonction de nettoyage des données
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// 3. Traitement de la requête POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 4. Récupération des données
    $nom_complet = sanitize($_POST["nom_complet"]);
    $telephone = sanitize($_POST["telephone"]);
    $email = sanitize($_POST["email"]);
    $date_naissance = sanitize($_POST["date_naissance"]);
    $genre = sanitize($_POST["genre"]);
    $biographie = sanitize($_POST["biographie"]);
    $accord = isset($_POST["accord"]) ? 1 : 0;
    $langages = isset($_POST["langages"]) && is_array($_POST["langages"]) ? $_POST["langages"] : [];

    // 5. Validation avec regex
    $erreurs = [];

    // Nom : lettres, espaces, tirets
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-]+$/u", $nom_complet)) {
        $erreurs["nom_complet"] = "Seules les lettres, espaces et tirets sont autorisés.";
    } elseif (strlen($nom_complet) > 150) {
        $erreurs["nom_complet"] = "Le nom complet ne doit pas dépasser 150 caractères.";
    }

    // Téléphone
    if (!preg_match("/^[0-9\s\-\+]+$/", $telephone)) {
        $erreurs["telephone"] = "Chiffres, espaces, tirets et '+' uniquement.";
    } elseif (strlen($telephone) < 7) {
        $erreurs["telephone"] = "Le numéro doit comporter au moins 7 chiffres.";
    }

    // Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs["email"] = "Email invalide.";
    }

    // Genre
    if (!in_array($genre, ["masculin", "feminin"])) {
        $erreurs["genre"] = "Genre non valide.";
    }

    // Langages
    $langages_autorises = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskel", "Clojure", "Prolog", "Scala", "Go"];
    if (empty($langages)) {
        $erreurs["langages"] = "Vous devez sélectionner au moins un langage.";
    } else {
        foreach ($langages as $langage) {
            if (!in_array($langage, $langages_autorises)) {
                $erreurs["langages"] = "Langage non autorisé : " . htmlspecialchars($langage);
                break;
            }
        }
    }

    // Biographie
    if (!preg_match("/^[a-zA-ZÀ-ÿ0-9\s\.\,\-\?!'\"\(\)]+$/u", $biographie)) {
        $erreurs["biographie"] = "Caractères non valides dans la biographie.";
    }

    // Accord
    if (!$accord) {
        $erreurs["accord"] = "Vous devez accepter le contrat.";
    }

    // 6. S'il y a des erreurs → cookies + redirection
    if (!empty($erreurs)) {
        setcookie("form_errors", json_encode($erreurs), 0, "/");
        setcookie("form_values", json_encode($_POST), 0, "/");
        header("Location: index.php");
        exit();
    }

    // 7. Connexion à la BDD
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }

    // 8. Insertion utilisateur
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nom_complet, telephone, email, date_naissance, genre, biographie, accord) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom_complet, $telephone, $email, $date_naissance, $genre, $biographie, $accord]);
        $utilisateur_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de l'utilisateur : " . $e->getMessage());
    }

    // 9. Insertion langages
    try {
        $stmt = $pdo->prepare("INSERT INTO user_languages (utilisateur_id, langage) VALUES (?, ?)");
        foreach ($langages as $langage) {
            $stmt->execute([$utilisateur_id, $langage]);
        }
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement des langages : " . $e->getMessage());
    }

    // 10. Cookies succès pour un an
    setcookie("form_success_values", json_encode($_POST), time() + 365 * 24 * 60 * 60, "/");

    // 11. Redirection avec succès
    header("Location: index.php?success=1");
    exit();
}
?>
