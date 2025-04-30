<?php

// 1. Configuration de la base de données
$host = "localhost"; // Ou votre hôte
$username = "u68658";
$password = "7975806";
$database = "u68658";

// 2. Fonction pour sécuriser les données (très important !)
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// 3. Traitement de la requête POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Récupération et sécurisation des données du formulaire
    $nom_complet = sanitize($_POST["nom_complet"]);
    $telephone = sanitize($_POST["telephone"]);
    $email = sanitize($_POST["email"]);
    $date_naissance = sanitize($_POST["date_naissance"]);
    $genre = sanitize($_POST["genre"]);
    $biographie = sanitize($_POST["biographie"]);
    $accord = isset($_POST["accord"]) ? 1 : 0; // La checkbox renvoie une valeur uniquement si elle est cochée

    // Traitement de la sélection des langages de programmation
    if (isset($_POST["langages"]) && is_array($_POST["langages"])) {
        $langages = $_POST["langages"];
    } else {
        $langages = []; // Tableau vide si rien n'est sélectionné
    }
    
    
    // 5. Validation des données (TRÈS IMPORTANT !)
    $erreurs = [];

    // Validation du nom complet
    if (!preg_match("/^[a-zA-Zà-ÿ\s-]+$/u", $nom_complet)) {
        $erreurs[] = "Le champ Nom complet doit contenir uniquement des lettres, des espaces et des tirets.";
    }
    if (strlen($nom_complet) > 150) {
        $erreurs[] = "Le champ Nom complet ne doit pas dépasser 150 caractères.";
    }

    // Validation du genre
    $genres_autorises = ["masculin", "feminin"];
    if (!in_array($genre, $genres_autorises)) {
        $erreurs[] = "Valeur de genre non valide.";
    }

    // Validation des langages de programmation
    $langages_autorises = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskel", "Clojure", "Prolog", "Scala", "Go"];
    foreach ($langages as $langage) {
        if (!in_array($langage, $langages_autorises)) {
            $erreurs[] = "Langage de programmation non valide : " . htmlspecialchars($langage); // Pas besoin de sanitize ici, car on utilise les valeurs de $langages_autorises
        }
    }
    if (empty($langages)) {
        $erreurs[] = "Vous devez sélectionner au moins un langage de programmation.";
    }

    // Validation de l'email (peut être plus stricte)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Format d'email incorrect.";
    }

    //Validation du téléphone (vérification simple de chiffres et de la longueur)
    if(!preg_match("/^[0-9\s\-\+]+$/", $telephone)) {
        $erreurs[] = "Le numéro de téléphone doit contenir uniquement des chiffres, espaces, tirets et signes +. ";
    }
    if(strlen($telephone) < 7) {
        $erreurs[] = "Le numéro de téléphone doit contenir au moins 7 chiffres. ";
    }

    // 6. Si des erreurs sont présentes, les afficher
   // 6. Si des erreurs sont présentes, les stocker dans des cookies et rediriger vers le formulaire
if (!empty($erreurs)) {
    setcookie("form_errors", json_encode($erreurs), 0, "/"); // Expire à la fin de la session
    setcookie("form_values", json_encode($_POST), 0, "/");
    header("Location: index.php");
    exit();
}

    } else {

        // 7. Connexion à la base de données (en utilisant PDO - option recommandée)
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Activer l'affichage des erreurs
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }

        // 8. Préparation de la requête pour la table principale (données de l'utilisateur)
        $sql_utilisateur = "INSERT INTO users (nom_complet, telephone, email, date_naissance, genre, biographie, accord) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_utilisateur = $pdo->prepare($sql_utilisateur);

        // 9. Exécution de la requête (table principale)
        try {
            $stmt_utilisateur->execute([$nom_complet, $telephone, $email, $date_naissance, $genre, $biographie, $accord]);
            $utilisateur_id = $pdo->lastInsertId(); // Récupérer l'ID de l'enregistrement inséré
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage());
        }

        // 10. Préparation de la requête pour la table de liaison (langages de programmation)
        $sql_langages = "INSERT INTO user_languages (utilisateur_id, langage) VALUES (?, ?)";
        $stmt_langages = $pdo->prepare($sql_langages);

        // 11. Exécution de la requête (pour chaque langage)
        try {
            foreach ($langages as $langage) {
                $stmt_langages->execute([$utilisateur_id, $langage]);
            }
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout des langages : " . $e->getMessage());
        }

        // 12. Affichage d'un message de succès
       // 12. Enregistrement des données réussies dans un cookie pour 1 an
setcookie("form_success_values", json_encode($_POST), time() + 365 * 24 * 60 * 60, "/");

// 13. Rediriger vers index.php (pour afficher le message de succès et remplir les valeurs par défaut)
header("Location: index.php?success=1");
exit();

    }
}
?>