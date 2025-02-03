<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../styles/profil.css">
</head>
<body>
    <div class="profil-container">
        <div class="coordonnees-container">
            <h2>Coordonnées</h2>
            <form id="profileForm">
                <label for="nom">Nom :</label><br>
                <input type="text" id="nom" name="nom" class="input-field" placeholder=""><br>

                <label for="prénom">Prénom : </label><br>
                <input type="text" id="prénom" name="prénom" class="input-field" placeholder=""><br>
                
                <label for="email">Email :</label><br>
                <input type="email" id="email" name="email" class="input-field" placeholder=""><br>
                
                <button type="submit">Valider</button>
            </form>
        </div>

        <div class="password-container">
            <h2>Changer de mot de passe</h2>
            <form id="new_password">
            <input type="password" placeholder="Mot de passe actuel" class="input-field"><br><br>
            <input type="password" placeholder="Nouveau mot de passe" class="input-field"><br><br>
            <button type="submit">Valider</button>
            </form>
        </div>
    </div>
</body>
</html>